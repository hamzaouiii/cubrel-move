<?php

namespace App\Http\Controllers;

use App\Models\EmailCaptureAddress;
use App\Models\Modules\Contact;
use App\Models\Modules\Email;
use App\Services\Relationships\RelationshipService;
use App\Services\Users\EmailCaptureAddressService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\Response;

/**
 * Receives Mailtrap's inbound-email webhook for BCC-captured mail.
 *
 * Mailtrap's webhook is a lightweight notification, not the full parsed
 * email: it POSTs {"events": [{"event": "inbound.message_received",
 * "inbox_id": ..., "message_id": ..., "from": "Name <email>", ...}]},
 * signed via HMAC-SHA256 in the "mailtrap-signature" header. The full
 * subject/body/recipients are fetched separately via
 * GET /api/inbound/inboxes/{inbox_id}/messages/{message_id} — confirmed
 * against a live webhook payload + docs.mailtrap.io on 2026-07-30.
 */
class EmailInboundWebhookController extends Controller
{
    protected const SIGNATURE_HEADER = 'mailtrap-signature';

    public function __construct(protected EmailCaptureAddressService $addresses)
    {
    }

    public function handle(Request $request): Response
    {
        if (! $this->hasValidSignature($request)) {
            Log::warning('Rejected inbound email webhook: bad signature.');

            return response('', 401);
        }

        foreach ((array) $request->input('events', []) as $event) {
            if (($event['event'] ?? null) !== 'inbound.message_received') {
                continue;
            }

            $this->processMessage((int) $event['inbox_id'], (string) $event['message_id']);
        }

        return response('', 200);
    }

    protected function processMessage(int $inboxId, string $messageId): void
    {
        if (Email::where('provider_message_id', $messageId)->exists()) {
            // Mailtrap delivers at-least-once — a retry of an already
            // processed message is a no-op, not an error.
            return;
        }

        $message = $this->fetchMessage($inboxId, $messageId);

        if (! $message) {
            Log::warning('Inbound email webhook: could not fetch message.', compact('inboxId', 'messageId'));

            return;
        }

        // TEMPORARY, remove once the shape is nailed down — the webhook
        // event's "from" was a plain string but the Messages API's "to"
        // turned out to be an array, so log the raw shape while we adapt.
        Log::debug('Mailtrap message payload', ['message' => $message]);

        $fromList = $this->normalizeAddressList($message['from'] ?? null);
        $toList = $this->normalizeAddressList($message['to'] ?? null);
        $ccList = $this->normalizeAddressList($message['cc'] ?? null);

        $recipient = $toList[0]['email'] ?? null;
        $mailbox = $recipient ? $this->resolveMailbox($recipient) : null;

        if (! $mailbox) {
            Log::info('Inbound email webhook: no address matched recipient.', ['recipient' => $recipient]);

            return;
        }

        $fromAddress = $fromList[0]['email'] ?? null;
        $fromName = $fromList[0]['name'] ?? null;

        $email = Email::create([
            'name' => $message['subject'] ?: '(no subject)',
            'body' => $message['text_body'] ?? $message['html_body'] ?? null,
            'from_address' => $fromAddress,
            'from_name' => $fromName,
            'to_addresses' => array_column($toList, 'email'),
            'cc_addresses' => array_column($ccList, 'email'),
            'sent_at' => isset($message['received_at']) ? Carbon::parse($message['received_at']) : now(),
            'direction' => 'logged',
            'provider_message_id' => $messageId,
            'mailbox' => $mailbox['slug'],
            // Null owner_id on a team address falls through to BaseModule's
            // default owner (same as any other module), not an error case.
            'owner_id' => $mailbox['owner_id'],
        ]);

        $this->linkMatchingContacts($email, array_filter(array_merge(
            [$fromAddress],
            array_column($toList, 'email'),
            array_column($ccList, 'email'),
        )));
    }

    /**
     * Resolves an inbound recipient address to whichever mailbox owns it:
     * a user's personal username-based address first, then an
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

        // Lowercased for the same reason as EmailCaptureAddressService's
        // username lookup — SMTP clients don't reliably lowercase
        // local-parts. Slugs are already enforced lowercase at creation
        // (EmailCaptureAddressController's validation regex), so a plain
        // exact match against the normalized incoming value is enough.
        $localPart = Str::lower(Str::before($recipient, '@'));
        $address = EmailCaptureAddress::where('slug', $localPart)->first();

        if ($address) {
            return ['slug' => $address->slug, 'owner_id' => $address->owner_id];
        }

        return null;
    }

    /**
     * GET /api/inbound/inboxes/{inbox_id}/messages/{message_id} — returns
     * the full parsed email the webhook only notified us about.
     */
    protected function fetchMessage(int $inboxId, string $messageId): ?array
    {
        $token = config('services.mailtrap.api_token');

        $request = Http::withHeaders(['Api-Token' => $token]);

        // Local Windows PHP installs commonly lack a configured CA bundle,
        // which cURL needs to verify Mailtrap's certificate. Skip
        // verification only in local dev — never in production, where the
        // server's CA bundle is properly configured.
        if (app()->environment('local')) {
            $request = $request->withoutVerifying();
        }

        $response = $request->get("https://mailtrap.io/api/inbound/inboxes/{$inboxId}/messages/{$messageId}");

        if (! $response->successful()) {
            Log::warning('Mailtrap messages API request failed.', [
                'status' => $response->status(),
                'body' => $response->body(),
            ]);

            return null;
        }

        return $response->json();
    }

    /**
     * Case-insensitive lookup against the indexed contacts.email column
     * (see 2026_07_29_120001_add_email_index_to_contacts_table.php),
     * linking every match via the generic activity relationship
     * (emails is_activity => contacts has_activity) rather than a
     * bespoke pivot.
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

    protected function hasValidSignature(Request $request): bool
    {
        $secret = config('services.mailtrap.inbound_webhook_secret');

        if (! $secret) {
            return false;
        }

        $signature = $request->header(self::SIGNATURE_HEADER);

        if (! $signature) {
            return false;
        }

        $expected = hash_hmac('sha256', $request->getContent(), $secret);

        return hash_equals($expected, $signature);
    }

    /**
     * Mailtrap isn't consistent about from/to/cc shape between the webhook
     * event (plain "Name <email>" string) and the Messages API response
     * (array — of strings, or of {email, name} objects, or a single
     * {email, name} object for a single recipient — exact shape still
     * being confirmed against live payloads). Normalizes any of those into
     * a flat list of ['email' => ..., 'name' => ...].
     */
    protected function normalizeAddressList(mixed $raw): array
    {
        if ($raw === null || $raw === '') {
            return [];
        }

        // A single {email, name}-shaped object, not a list of them.
        if (is_array($raw) && array_key_exists('email', $raw)) {
            $raw = [$raw];
        }

        if (is_string($raw)) {
            $raw = explode(',', $raw);
        }

        return collect($raw)
            ->map(function ($item) {
                if (is_array($item)) {
                    return ['email' => $item['email'] ?? null, 'name' => $item['name'] ?? null];
                }

                [$email, $name] = $this->splitAddress((string) $item);

                return ['email' => $email, 'name' => $name];
            })
            ->filter(fn (array $a) => filled($a['email']))
            ->values()
            ->all();
    }

    /**
     * Parses an RFC-2822-style "Name <email>" string (or a bare address)
     * into [email, name].
     */
    protected function splitAddress(string $raw): array
    {
        if (preg_match('/^(.*?)<(.+?)>$/', trim($raw), $matches)) {
            return [trim($matches[2]), trim($matches[1], " \t\"") ?: null];
        }

        return [trim($raw) ?: null, null];
    }
}
