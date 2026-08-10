<?php

namespace App\Http\Controllers;

use App\Models\EmailCaptureAddress;
use App\Models\Modules\Contact;
use App\Models\Modules\Email;
use App\Services\Relationships\RelationshipService;
use App\Services\Users\EmailCaptureAddressService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\Response;
use ZBateson\MailMimeParser\Header\AddressHeader;
use ZBateson\MailMimeParser\Header\IHeader;
use ZBateson\MailMimeParser\MailMimeParser;

/**
 * Receives inbound email relayed by the self-hosted Postfix catch-all
 * (see deploy/postfix/ and deploy/cubrel-inbound-relay.sh) for every
 * *.cubrel.com tenant domain. Postfix accepts SMTP for all of them and
 * pipes each message to a thin relay script, which POSTs the raw RFC822
 * body here along with the original SMTP envelope recipient in a header
 * — the only reliable source of the actual capture address, since a
 * BCC'd address never appears in the message's own To:/Cc: headers.
 *
 * Trust model: the relay runs on infrastructure we control (not a third
 * party across the internet), so verification is a shared secret header
 * rather than a per-provider HMAC scheme — see hasValidSecret().
 */
class EmailInboundWebhookController extends Controller
{
    protected const SECRET_HEADER = 'X-Cubrel-Relay-Secret';

    protected const RECIPIENT_HEADER = 'X-Cubrel-Relay-Recipient';

    public function __construct(protected EmailCaptureAddressService $addresses)
    {
    }

    public function handle(Request $request): Response
    {
        if (! $this->hasValidSecret($request)) {
            Log::warning('Rejected inbound email relay: bad or missing secret.');

            return response('', 401);
        }

        $recipient = $request->header(self::RECIPIENT_HEADER);

        if (! $recipient) {
            Log::warning('Inbound email relay: missing recipient header.');

            return response('', 400);
        }

        $mailbox = $this->resolveMailbox($recipient);

        if (! $mailbox) {
            // Not one of ours — acknowledge so the relay script doesn't
            // treat this as a failure worth retrying, but nothing gets
            // created. Deliberately 202 not 404: doesn't confirm or deny
            // which addresses are valid to whoever sent it.
            Log::info('Inbound email relay: no address matched recipient.', ['recipient' => $recipient]);

            return response('', 202);
        }

        $raw = $request->getContent();
        $message = (new MailMimeParser())->parse($raw, false);

        // RFC 5322 Message-ID is globally unique per message. Falls back
        // to a content hash for the rare message that omits it, so a
        // retried relay delivery still can't double-create a record.
        $messageId = $message->getMessageId() ?: hash('sha256', $raw);

        if (Email::where('provider_message_id', $messageId)->exists()) {
            return response('', 200);
        }

        $fromHeader = $message->getHeader('from');
        $fromAddress = $fromHeader instanceof AddressHeader ? $fromHeader->getEmail() : null;
        $fromName = $fromHeader instanceof AddressHeader ? $fromHeader->getPersonName() : null;

        $toAddresses = $this->extractAddresses($message->getHeader('to'));
        $ccAddresses = $this->extractAddresses($message->getHeader('cc'));

        $email = Email::create([
            'name' => $message->getSubject() ?: '(no subject)',
            'body' => $message->getTextContent() ?? $message->getHtmlContent(),
            'from_address' => $fromAddress,
            'from_name' => $fromName,
            'to_addresses' => $toAddresses,
            'cc_addresses' => $ccAddresses,
            'sent_at' => $this->parseDate($message->getHeaderValue('date')),
            'provider_message_id' => $messageId,
            'mailbox' => $mailbox['slug'],
            // Null owner_id on a team address falls through to BaseModule's
            // default owner, same as any other module.
            'owner_id' => $mailbox['owner_id'],
        ]);

        $this->linkMatchingContacts($email, array_filter(array_merge(
            [$fromAddress],
            $toAddresses,
            $ccAddresses,
        )));

        return response('', 200);
    }

    /**
     * Resolves the envelope recipient to whichever mailbox owns it: a
     * user's personal username-based address first, then an
     * admin-created EmailCaptureAddress (which may be ownerless — a team
     * mailbox like "leads"). Returns null if nothing matches.
     *
     * @return array{slug: string, owner_id: ?string}|null
     */
    protected function resolveMailbox(string $recipient): ?array
    {
        $user = $this->addresses->findUserByRecipientAddress($recipient);

        if ($user) {
            return ['slug' => $user->username, 'owner_id' => $user->id];
        }

        $localPart = Str::lower(Str::before($recipient, '@'));
        $address = EmailCaptureAddress::where('slug', $localPart)->first();

        if ($address) {
            return ['slug' => $address->slug, 'owner_id' => $address->owner_id];
        }

        return null;
    }

    /**
     * @return string[]
     */
    protected function extractAddresses(?IHeader $header): array
    {
        if (! $header instanceof AddressHeader) {
            return [];
        }

        return array_values(array_filter(array_map(
            fn ($part) => $part->getEmail(),
            $header->getAddresses(),
        )));
    }

    protected function parseDate(?string $raw): Carbon
    {
        if (! $raw) {
            return now();
        }

        try {
            return Carbon::parse($raw);
        } catch (\Exception) {
            return now();
        }
    }

    /**
     * Case-insensitive lookup against the indexed contacts.email column
     * (see 2025_12_04_115225_create_contacts_table.php), linking every
     * match via the generic activity relationship (emails is_activity =>
     * contacts has_activity) rather than a bespoke pivot.
     */
    protected function linkMatchingContacts(Email $email, array $addresses): void
    {
        $addresses = array_unique(array_map(fn (string $a) => Str::lower($a), $addresses));

        if (empty($addresses)) {
            return;
        }

        Contact::query()
            ->whereIn('email', $addresses)
            ->get()
            ->each(fn (Contact $contact) => RelationshipService::link(
                'contacts_emails',
                'emails',
                $email->id,
                $contact->id,
            ));
    }

    protected function hasValidSecret(Request $request): bool
    {
        $secret = config('services.inbound_relay.secret');

        if (! $secret) {
            return false;
        }

        $provided = $request->header(self::SECRET_HEADER);

        if (! $provided) {
            return false;
        }

        return hash_equals($secret, $provided);
    }
}
