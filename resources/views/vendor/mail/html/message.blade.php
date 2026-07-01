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

    {{-- Footer (content lives in footer.blade.php — this slot wires it in) --}}
    @slot('footer')
        @component('mail::footer')
        @endcomponent
    @endslot
@endcomponent
