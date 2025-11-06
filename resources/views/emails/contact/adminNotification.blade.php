@component('mail::message')
# New Lead from the Contact form

**Name:** {{ $msg->name }}  
**E-Mail:** {{ $msg->email }}  
@isset($msg->phone)
**Phone:** {{ $msg->phone }}  
@endisset

**Message:**
@component('mail::panel')
{{ $msg->message }}
@endcomponent

_Sent On {{ $msg->created_at->format('d.m.Y H:i') }}_
@endcomponent
