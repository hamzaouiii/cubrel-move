@component('mail::message')
# Vielen Dank für Ihre Nachricht

Hallo {{ $contactMessage->name }},

wir haben Ihre Nachricht erhalten und melden uns so bald wie möglich bei Ihnen.

@component('mail::panel')
**Ihre Nachricht:**
> {{ $contactMessage->message }}
@endcomponent

Mit freundlichen Grüßen  
**{{ config('app.name') }}**  
[{{ config('app.url') }}]({{ config('app.url') }})
@endcomponent
