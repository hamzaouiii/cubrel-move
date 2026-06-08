@php
    $renderer         ??= app(\App\Support\PdfValueRenderer::class);
    $sections         ??= [];
    $lineItems        ??= [];
    $relationshipData ??= [];
    $fieldMap = collect($fields)->keyBy('name');

    $resolveField = function (string $key) use ($record, $fieldMap, $renderer): string {
        $value = $record[$key] ?? null;
        if ($value === null || $value === '') return '—';
        $field = $fieldMap->get($key);
        if (!$field) return (string) $value;
        $dropdownValues = $field->dropdown_list?->values ?? null;
        return $renderer->render($field->type, $value, $dropdownValues);
    };

    // Resolve a field item — uses related record if item has a 'relationship' key
    $resolveItem = function (array $item) use ($resolveField, $relationshipData, $renderer, $fieldMap): string {
        $relName = $item['relationship'] ?? null;
        if ($relName) {
            $relRecord = ($relationshipData[$relName] ?? [])[0] ?? [];
            $value = $relRecord[$item['name']] ?? null;
            if ($value === null || $value === '') return '—';
            $field = $fieldMap->get($item['name']);
            if (!$field) return (string) $value;
            $dropdownValues = $field->dropdown_list?->values ?? null;
            return $renderer->render($field->type, $value, $dropdownValues);
        }
        return $resolveField($item['name']);
    };

    // Company initials fallback
    $ciWords = preg_split('/\s+/', trim($company['name'] ?? ''));
    $companyInitials = strtoupper(
        mb_substr($ciWords[0] ?? '', 0, 1) . mb_substr($ciWords[1] ?? '', 0, 1)
    ) ?: 'CO';

    // Row grouping for half-width pairs
    $sectionArr = array_values($sections);
    $rows = [];
    $i = 0;
    while ($i < count($sectionArr)) {
        $s = $sectionArr[$i];
        $halfable = in_array($s['type'] ?? '', ['fields', 'relationship']);
        if ($halfable && ($s['width'] ?? 'full') === 'half') {
            $nxt = $sectionArr[$i + 1] ?? null;
            if ($nxt && in_array($nxt['type'] ?? '', ['fields', 'relationship']) && ($nxt['width'] ?? 'full') === 'half') {
                $rows[] = [$s, $nxt];
                $i += 2;
                continue;
            }
        }
        $rows[] = [$s];
        $i++;
    }
@endphp
<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
<style>
* { margin: 0; padding: 0; box-sizing: border-box; }
body { font-family: DejaVu Sans, sans-serif; font-size: 12px; color: #333; line-height: 1.4; }
.page { padding: 40px 48px 56px; }

/* ── Header ─────────────────────────────────────────────────── */
.hdr           { width: 100%; border-collapse: collapse; margin-bottom: 30px; }
.hdr-logo-img  { max-height: 52px; max-width: 160px; display: block; margin-bottom: 8px; }
.hdr-logo-box  {
    width: 44px; height: 44px; background: #4f46e5; border-radius: 8px;
    text-align: center; line-height: 44px; color: #fff;
    font-size: 14px; font-weight: bold; margin-bottom: 8px;
}
.hdr-co-name   { font-size: 15px; font-weight: bold; color: #111; margin-bottom: 3px; }
.hdr-co-meta   { font-size: 10px; color: #666; line-height: 1.75; }
.hdr-doc-title { font-size: 26px; font-weight: bold; color: #111; text-align: right; line-height: 1.1; }
.hdr-doc-num   { font-size: 12px; color: #888; text-align: right; margin-top: 5px; }
.hdr-meta-row  { font-size: 11px; color: #444; text-align: right; margin-top: 4px; }
.hdr-meta-lbl  { color: #999; font-size: 10px; }

/* ── Section blocks ──────────────────────────────────────────── */
.sec-label {
    font-size: 9px; font-weight: bold; text-transform: uppercase;
    letter-spacing: 0.7px; color: #888; margin-bottom: 7px;
}
.sec-sep {
    border: none; border-bottom: 1px solid #e5e7eb;
    margin-bottom: 12px; padding-bottom: 5px;
}
.sec-block { margin-bottom: 22px; }

/* ── Fields: horizontal label-above-value (full-width) ──────── */
.frow       { width: 100%; border-collapse: collapse; }
.fcell      { vertical-align: top; padding-right: 20px; }
.fcell-lbl  {
    font-size: 9px; font-weight: bold; text-transform: uppercase;
    letter-spacing: 0.6px; color: #888; margin-bottom: 4px;
}
.fcell-val  { font-size: 12px; font-weight: bold; color: #111; }

/* ── Fields: stacked values (half-width) ────────────────────── */
.fstack-val       { font-size: 12px; font-weight: bold; color: #111; margin-bottom: 2px; }
.fstack-val--sub  { font-size: 11px; font-weight: normal; color: #444; }

/* ── Text block ─────────────────────────────────────────────── */
.txtbox       { background: #f5f6f8; border-radius: 6px; padding: 12px 15px; margin-bottom: 22px; }
.txtbox-title {
    font-size: 9px; font-weight: bold; text-transform: uppercase;
    letter-spacing: 0.7px; color: #888; margin-bottom: 7px;
}
.txtbox-body  { font-size: 11px; color: #444; line-height: 1.75; white-space: pre-wrap; }

/* ── Divider ─────────────────────────────────────────────────── */
.divider { border: none; border-top: 1px solid #e5e7eb; margin: 14px 0; }

/* ── Relationship / generic table ───────────────────────────── */
.rtbl       { width: 100%; border-collapse: collapse; margin-bottom: 4px; }
.rtbl thead tr  { border-top: 1.5px solid #111; border-bottom: 1.5px solid #111; }
.rtbl thead th  { font-size: 10px; font-weight: bold; padding: 6px 6px; text-align: left;
                  color: #555; text-transform: uppercase; letter-spacing: 0.4px; }
.rtbl tbody tr  { border-bottom: 1px solid #e9ecef; }
.rtbl tbody td  { padding: 8px 6px; font-size: 11px; vertical-align: top; }

/* ── Line items table ────────────────────────────────────────── */
.litbl       { width: 100%; border-collapse: collapse; margin-bottom: 2px; }
.litbl thead tr { border-top: 1.5px solid #111; border-bottom: 1.5px solid #111; }
.litbl thead th { font-size: 10px; font-weight: bold; padding: 7px 6px; text-align: left;
                  color: #555; text-transform: uppercase; letter-spacing: 0.4px; }
.litbl thead th.r { text-align: right; }
.litbl tbody tr { border-bottom: 1px solid #e9ecef; }
.litbl tbody td { padding: 9px 6px; font-size: 11px; vertical-align: top; }
.litbl tbody td.r { text-align: right; }
.li-name     { font-weight: 600; color: #111; }
.li-note     { font-size: 10px; color: #777; margin-top: 2px; }

/* ── Totals ──────────────────────────────────────────────────── */
.totals-wrap { width: 100%; border-collapse: collapse; margin: 8px 0 24px; }
.ttbl        { border-collapse: collapse; width: 100%; }
.ttbl td     { padding: 4px 0; font-size: 11px; }
.t-lbl       { color: #555; padding-right: 28px; text-align: left; }
.t-val       { text-align: right; font-weight: 500; color: #111; white-space: nowrap; }
.t-neg       { color: #ef4444; }
.t-grand td  { border-top: 1.5px solid #111; padding-top: 8px; font-size: 13px; font-weight: bold; color: #111; }

/* ── Footer ──────────────────────────────────────────────────── */
.footer-wrap { position: fixed; bottom: 0; left: 0; right: 0; background: #fff; padding: 0 48px 14px; }
.footer      { width: 100%; border-collapse: collapse; border-top: 1px solid #d1d5db; }
.footer td   { font-size: 9px; color: #999; vertical-align: top; padding-top: 8px; line-height: 1.65; }
</style>
</head>
<body>
<div class="page">

@foreach($rows as $rowSections)

{{-- ═══════════════════════════════════════════════════════════
     Two half-width sections side by side
════════════════════════════════════════════════════════════════ --}}
@if(count($rowSections) === 2)
@php [$secA, $secB] = $rowSections; @endphp
<table style="width:100%; border-collapse:collapse; margin-bottom:22px;"><tr>

{{-- Left half --}}
<td style="width:50%; vertical-align:top; padding-right:16px;">
@php $sec = $secA; $secType = $sec['type'] ?? ''; @endphp
@if($secType === 'fields')
@php $fItems = array_values(array_filter($sec['items'] ?? [], fn($x) => ($x['kind'] ?? '') === 'field')); @endphp
@if(count($fItems))
<div class="sec-block">
@if(!empty($sec['name']))<div class="sec-label">{{ __($sec['name']) }}</div>@endif
@foreach($fItems as $fItem)
<div class="fcell-lbl" style="margin-bottom:2px;">{{ $fItem['label'] ? __($fItem['label']) : $fItem['name'] }}</div>
<div class="fstack-val" style="margin-bottom:8px;">{{ $resolveItem($fItem) }}</div>
@endforeach
</div>
@endif
@elseif($secType === 'relationship')
@php $relRecs = $relationshipData[$sec['relationship'] ?? ''] ?? []; $relCols = $sec['columns'] ?? []; @endphp
@if(!empty($relCols) && !empty($relRecs))
<div class="sec-block">
@if(!empty($sec['label']))<div class="sec-label">{{ __($sec['label']) }}</div>@endif
<table class="rtbl"><thead><tr>@foreach($relCols as $c)<th>{{ $c['label'] ? __($c['label']) : $c['name'] }}</th>@endforeach</tr></thead>
<tbody>@foreach($relRecs as $rr)<tr>@foreach($relCols as $c)<td>{{ $rr[$c['name']] ?? '—' }}</td>@endforeach</tr>@endforeach</tbody></table>
</div>
@endif
@endif
</td>

{{-- Right half --}}
<td style="width:50%; vertical-align:top;">
@php $sec = $secB; $secType = $sec['type'] ?? ''; @endphp
@if($secType === 'fields')
@php $fItems = array_values(array_filter($sec['items'] ?? [], fn($x) => ($x['kind'] ?? '') === 'field')); @endphp
@if(count($fItems))
<div class="sec-block">
@if(!empty($sec['name']))<div class="sec-label">{{ __($sec['name']) }}</div>@endif
@foreach($fItems as $fItem)
<div class="fcell-lbl" style="margin-bottom:2px;">{{ $fItem['label'] ? __($fItem['label']) : $fItem['name'] }}</div>
<div class="fstack-val" style="margin-bottom:8px;">{{ $resolveItem($fItem) }}</div>
@endforeach
</div>
@endif
@elseif($secType === 'relationship')
@php $relRecs = $relationshipData[$sec['relationship'] ?? ''] ?? []; $relCols = $sec['columns'] ?? []; @endphp
@if(!empty($relCols) && !empty($relRecs))
<div class="sec-block">
@if(!empty($sec['label']))<div class="sec-label">{{ __($sec['label']) }}</div>@endif
<table class="rtbl"><thead><tr>@foreach($relCols as $c)<th>{{ $c['label'] ? __($c['label']) : $c['name'] }}</th>@endforeach</tr></thead>
<tbody>@foreach($relRecs as $rr)<tr>@foreach($relCols as $c)<td>{{ $rr[$c['name']] ?? '—' }}</td>@endforeach</tr>@endforeach</tbody></table>
</div>
@endif
@endif
</td>

</tr></table>

{{-- ═══════════════════════════════════════════════════════════
     Single full-width section
════════════════════════════════════════════════════════════════ --}}
@else
@php $section = $rowSections[0]; $type = $section['type'] ?? ''; @endphp

{{-- ── Header ─────────────────────────────────────────────── --}}
@if($type === 'header')
<table class="hdr">
<tr>
    <td style="width:55%; vertical-align:top;">
        @if(!empty($company['logo_url']))
            <img src="{{ $company['logo_url'] }}" class="hdr-logo-img" alt="" />
        @else
            <div class="hdr-logo-box">{{ $companyInitials }}</div>
        @endif
        <div class="hdr-co-name">{{ $company['name'] ?? '' }}</div>
        <div class="hdr-co-meta">
            @if(!empty($company['address'])){{ $company['address'] }}<br>@endif
            @php $contact = implode(' · ', array_filter([$company['phone'] ?? null, $company['email'] ?? null])); @endphp
            @if($contact){{ $contact }}@endif
        </div>
    </td>
    <td style="width:45%; vertical-align:top;">
        <div class="hdr-doc-title">{{ $section['title'] ?? $moduleLabel }}</div>
        @if(!empty($record['number']))
            <div class="hdr-doc-num"># {{ $record['number'] }}</div>
        @endif
        @foreach($section['items'] ?? [] as $hItem)
            @if(($hItem['kind'] ?? '') === 'text' && !empty($hItem['content']))
            <div class="hdr-meta-row">{{ $hItem['content'] }}</div>
            @endif
        @endforeach
    </td>
</tr>
</table>
@php $hFieldItems = array_values(array_filter($section['items'] ?? [], fn($x) => ($x['kind'] ?? '') === 'field')); @endphp
@if(count($hFieldItems))
<table style="width:100%;border-collapse:collapse;border-top:1px solid #e5e7eb;border-bottom:1px solid #e5e7eb;margin-bottom:20px;">
<tr>
@foreach($hFieldItems as $hItem)
<td style="vertical-align:top;padding:10px 16px 10px 0;">
    <div class="fcell-lbl">{{ $hItem['label'] ? __($hItem['label']) : $hItem['name'] }}</div>
    <div class="fcell-val">{{ $resolveField($hItem['name']) }}</div>
</td>
@endforeach
</tr>
</table>
@endif

{{-- ── Footer ─────────────────────────────────────────────── --}}
@elseif($type === 'footer')
<div class="footer-wrap">
<table class="footer">
<tr>
    <td style="width:65%;">
        {{ $company['name'] ?? '' }}
        @if(!empty($company['address'])) · {{ $company['address'] }}@endif
        @if(!empty($company['website'])) · {{ $company['website'] }}@endif
    </td>
    <td style="width:35%; text-align:right;">
        {{ __('pdf.generated_on', ['date' => now()->translatedFormat('M d, Y')]) }}
    </td>
</tr>
</table>
</div>

{{-- ── Fields (full-width: horizontal label-above-value) ──── --}}
@elseif($type === 'fields')
@php
    $fItems = array_values(array_filter($section['items'] ?? [], fn($x) => ($x['kind'] ?? '') === 'field'));
@endphp
@if(count($fItems))
<div class="sec-block">
@if(!empty($section['name']))
    <div class="sec-label sec-sep">{{ __($section['name']) }}</div>
@endif
<table class="frow">
<tr>
@foreach($fItems as $fItem)
<td class="fcell">
    <div class="fcell-lbl">{{ $fItem['label'] ? __($fItem['label']) : $fItem['name'] }}</div>
    <div class="fcell-val">{{ $resolveItem($fItem) }}</div>
</td>
@endforeach
</tr>
</table>
</div>
@endif

{{-- ── Text block ──────────────────────────────────────────── --}}
@elseif($type === 'text')
@if(!empty($section['content']))
<div class="txtbox">
@if(!empty($section['name']))<div class="txtbox-title">{{ __($section['name']) }}</div>@endif
<div class="txtbox-body">{{ $section['content'] }}</div>
</div>
@endif

{{-- ── Divider ─────────────────────────────────────────────── --}}
@elseif($type === 'divider')
<hr class="divider">

{{-- ── Relationship (full-width) ──────────────────────────── --}}
@elseif($type === 'relationship')
@php
    $relName    = $section['relationship'] ?? '';
    $relCols    = $section['columns'] ?? [];
    $relRecords = $relationshipData[$relName] ?? [];
@endphp
@if(!empty($relCols) && !empty($relRecords))
<div class="sec-block">
@if(!empty($section['label']))<div class="sec-label">{{ __($section['label']) }}</div>@endif
<table class="rtbl">
<thead><tr>
@foreach($relCols as $col)<th>{{ $col['label'] ? __($col['label']) : $col['name'] }}</th>@endforeach
</tr></thead>
<tbody>
@foreach($relRecords as $relRow)
<tr>@foreach($relCols as $col)<td>{{ $relRow[$col['name']] ?? '—' }}</td>@endforeach</tr>
@endforeach
</tbody>
</table>
</div>
@endif

{{-- ── Line items ──────────────────────────────────────────── --}}
@elseif($type === 'line_items')
@php
    $liSubtotal = $liDiscount = $liTax = $liTotal = 0;
    foreach ($lineItems as $li) {
        $liSubtotal += (float)($li['subtotal']        ?? 0);
        $liDiscount += (float)($li['discount_amount'] ?? 0);
        $liTax      += (float)($li['tax_amount']      ?? 0);
        $liTotal    += (float)($li['total']            ?? 0);
    }
    $currency = $record['currency'] ?? '';
    $liCols   = $section['columns'] ?? [];   // optional middle columns
@endphp
@if(count($lineItems))
<div class="sec-block">
<table class="litbl">
<thead>
<tr>
    <th style="width:4%;">#</th>
    <th>{{ __('pdf.name') }}</th>
    @foreach($liCols as $liCol)
    <th class="r">{{ $liCol['label'] ? __($liCol['label']) : $liCol['name'] }}</th>
    @endforeach
    <th class="r" style="width:11%;">{{ __('pdf.total') }}</th>
</tr>
</thead>
<tbody>
@foreach($lineItems as $liIdx => $liItem)
<tr>
    <td>{{ $liIdx + 1 }}</td>
    <td>
        <div class="li-name">{{ $liItem['name'] ?? '' }}</div>
        @if(!empty($liItem['note']))<div class="li-note">{{ $liItem['note'] }}</div>@endif
    </td>
    @foreach($liCols as $liCol)
    @php
        $raw = $liItem[$liCol['name']] ?? '';
        $fmt = is_numeric($raw) ? number_format((float)$raw, 2, '.', ',') : $raw;
        // append % for rate/percent fields
        if (is_numeric($raw) && preg_match('/rate|percent/i', $liCol['name'])) {
            $fmt .= '%';
        }
    @endphp
    <td class="r">{{ $raw !== '' ? $fmt : '—' }}</td>
    @endforeach
    <td class="r">{{ number_format((float)($liItem['total'] ?? 0), 2, '.', ',') }} {{ $currency }}</td>
</tr>
@endforeach
</tbody>
</table>

{{-- Totals --}}
<table class="totals-wrap">
<tr>
<td></td>
<td style="width:260px; padding:0; vertical-align:top;">
<table class="ttbl">
    <tr>
        <td class="t-lbl">{{ __('pdf.subtotal') }}</td>
        <td class="t-val">{{ number_format($liSubtotal, 2, '.', ',') }} {{ $currency }}</td>
    </tr>
    @if($liTax > 0)
    <tr>
        <td class="t-lbl">{{ __('pdf.tax_amount') }}</td>
        <td class="t-val">{{ number_format($liTax, 2, '.', ',') }} {{ $currency }}</td>
    </tr>
    @endif
    @if($liDiscount > 0)
    <tr>
        <td class="t-lbl">{{ __('pdf.discount_amount') }}</td>
        <td class="t-val t-neg">−{{ number_format($liDiscount, 2, '.', ',') }} {{ $currency }}</td>
    </tr>
    @endif
    <tr class="t-grand">
        <td class="t-lbl">{{ __('pdf.total') }}</td>
        <td class="t-val">{{ number_format($liTotal, 2, '.', ',') }} {{ $currency }}</td>
    </tr>
</table>
</td>
</tr>
</table>
</div>
@endif

@endif {{-- end single section type --}}
@endif {{-- end single vs paired --}}

@endforeach

</div>
</body>
</html>
