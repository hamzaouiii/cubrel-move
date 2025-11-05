@component('mail::message')
# Neue Kontaktanfrage

**Name:** {{ $msg->name }}  
**E-Mail:** {{ $msg->email }}  
@isset($msg->phone)
**Telefon:** {{ $msg->phone }}
@endisset

**Nachricht:**  
{{ $msg->message }}

_Gesendet am {{ $msg->created_at->format('d.m.Y H:i') }}_
@endcomponent
