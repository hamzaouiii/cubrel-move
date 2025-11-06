@component('mail::layout')
    {{-- Header --}}
    @slot('header')
        @component('mail::header', ['url' => config('app.url')])
            {{-- The header content is fully controlled in header.blade.php --}}
        @endcomponent
    @endslot

    {{-- Main --}}
    {{ $slot }}

    {{-- Subcopy (optional small print) --}}
    @isset($subcopy)
        @slot('subcopy')
            @component('mail::subcopy')
                {{ $subcopy }}
            @endcomponent
        @endslot
    @endisset

    {{-- Footer --}}
    @slot('footer')
        @component('mail::footer')
            © {{ date('Y') }} <a href="https://automatisierung-regensburg.de" style="color:#0d6efd; text-decoration:none;">automatisierung-regensburg.de</a>. Alle Rechte vorbehalten.
        @endcomponent
    @endslot
@endcomponent
