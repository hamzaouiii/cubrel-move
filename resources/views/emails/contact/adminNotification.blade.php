@component('mail::message')
# {{ __('emails.contact_admin.heading') }}

**{{ __('emails.contact_admin.label_name') }}:** {{ $msg->name }}
**{{ __('emails.contact_admin.label_email') }}:** {{ $msg->email }}
@isset($msg->phone)
**{{ __('emails.contact_admin.label_phone') }}:** {{ $msg->phone }}
@endisset

@component('mail::panel')
**{{ __('emails.contact_admin.label_message') }}:**

{{ $msg->message }}
@endcomponent

_{{ __('emails.contact_admin.sent_on', ['date' => $msg->created_at->format('d.m.Y H:i')]) }}_
@endcomponent
