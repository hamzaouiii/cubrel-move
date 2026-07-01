<table width="100%" border="0" cellpadding="0" cellspacing="0" role="presentation">
<tr>
<td align="center">
<table border="0" cellpadding="0" cellspacing="0" role="presentation">
<tr>
<td>
@php
    $btnColor = match($color ?? 'primary') {
        'success' => '#27ae60',
        'error'   => '#e74c3c',
        default   => \App\Support\Settings::get('primary_color', '#3498db'),
    };
@endphp
<a href="{{ $url }}" class="button" target="_blank"
   style="display:inline-block; border-radius:10px; background:{{ $btnColor }}; color:#ffffff; padding:12px 22px; font-weight:600; text-decoration:none;">
    {{ $slot }}
</a>
</td>
</tr>
</table>
</td>
</tr>
</table>
