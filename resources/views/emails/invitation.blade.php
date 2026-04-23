{{-- resources/views/emails/invitation.blade.php --}}
<x-mail::message>
# You've been invited

Someone invited you to join **Cubrel**. Click the button below to create your account.

<x-mail::button :url="$inviteUrl">
Accept Invitation
</x-mail::button>

This link expires on **{{ $expiresAt }}**.

If you weren't expecting this, you can ignore it.
</x-mail::message>