@component('mail::message')
# {{ __('emails.contact_confirmation.heading') }}

{{ __('emails.contact_confirmation.greeting', ['name' => $contactMessage->name]) }}

{{ __('emails.contact_confirmation.body') }}

@component('mail::panel')
**{{ __('emails.contact_confirmation.label') }}**

{{ $contactMessage->message }}
@endcomponent

{{ __('emails.contact_confirmation.regards') }}
**{{ config('app.name') }}**
[{{ config('app.url') }}]({{ config('app.url') }})
@endcomponent
