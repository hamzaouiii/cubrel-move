@php
  $isPreview ??= false;
  $renderer ??= app(\App\Support\PdfValueRenderer::class);
  $record ??= [];
  $sections ??= [];
  $lineItems ??= [];
  $currency ??= '';
  $currencySymbol = \App\Support\PdfNumberFormatter::symbol($currency);
  $relationshipData ??= [];
  $fieldMap         = collect($fields)->keyBy('name');
  $lineItemFieldMap = collect($lineItemFields ?? [])->keyBy('name');

  // For browser preview, embed WOFF2 fonts as base64 data URIs (cached for 30 days).
  // For PDF rendering, fonts are pre-registered by PdfController via DomPDF's PHP API
  // using local TTF files — no @font-face is needed in the CSS for that path.
  $fontFaceCSS = '';
  if ($isPreview) {
    $fontFaceCSS = cache()->remember('pdf_font_faces_woff2', now()->addDays(30), static function () {
      $dir  = resource_path('fonts');
      $defs = [
        ['Fira Sans', 300, 'fira-sans-v18-latin-300'],
        ['Fira Sans', 400, 'fira-sans-v18-latin-regular'],
        ['Fira Sans', 500, 'fira-sans-v18-latin-500'],
        ['Fira Sans', 600, 'fira-sans-v18-latin-600'],
        ['Fira Sans', 700, 'fira-sans-v18-latin-700'],
        ['Fira Sans', 900, 'fira-sans-v18-latin-900'],
        ['Heebo',     300, 'heebo-v28-latin-300'],
        ['Heebo',     400, 'heebo-v28-latin-regular'],
        ['Heebo',     500, 'heebo-v28-latin-500'],
        ['Heebo',     600, 'heebo-v28-latin-600'],
        ['Heebo',     700, 'heebo-v28-latin-700'],
        ['Heebo',     900, 'heebo-v28-latin-900'],
      ];
      $css = '';
      foreach ($defs as [$family, $weight, $base]) {
        $path = $dir . DIRECTORY_SEPARATOR . "{$base}.woff2";
        if (!file_exists($path)) continue;
        $b64  = base64_encode(file_get_contents($path));
        $css .= "@font-face{font-family:'{$family}';font-style:normal;font-weight:{$weight};"
             . "src:url('data:font/woff2;base64,{$b64}') format('woff2');}\n";
      }
      return $css;
    });
  }

  $scalarize = function (mixed $value): string {
    if (is_array($value))
      return implode(', ', array_filter(array_map('strval', $value)));
    return (string) $value;
  };

  $resolveField = function (string $key) use ($record, $fieldMap, $renderer, $scalarize, $isPreview): string {
    $value = $record[$key] ?? null;
    if ($value === null || $value === '')
      return $isPreview ? 'Sample' : '—';
    $field = $fieldMap->get($key);
    if (!$field)
      return $scalarize($value);
    $dropdownValues = $field->dropdown_list?->values ?? null;
    return $renderer->render($field->type, $value, $dropdownValues);
  };

  // Resolve a field item — uses related record if item has a 'relationship' key
  $resolveItem = function (array $item) use ($resolveField, $relationshipData, $renderer, $fieldMap, $scalarize, $isPreview): string {
    $relName = $item['relationship'] ?? null;
    if ($relName) {
      $relRecord = ($relationshipData[$relName] ?? [])[0] ?? [];
      $value = $relRecord[$item['name']] ?? null;
      if ($value === null || $value === '')
        return $isPreview ? 'Sample' : '—';
      $field = $fieldMap->get($item['name']);
      if (!$field)
        return $scalarize($value);
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

  // Extract document title (from first header/footer with a custom title)
  $docTitle = null;
  foreach ($sections as $_sec) {
    if (in_array($_sec['type'] ?? '', ['header', 'footer']) && !empty($_sec['title'])) {
      $docTitle = __($_sec['title']);
      break;
    }
  }
  $docTitle ??= __($moduleLabel);

  // Parse an address item directly from the raw record value and format as multiline HTML.
  // Bypasses the renderer so it works regardless of whether the field is in $fieldMap.
  $resolveAddress = function (array $item) use ($record, $relationshipData, $isPreview): string {
    $relName = $item['relationship'] ?? null;
    if ($relName) {
      $relRecord = ($relationshipData[$relName] ?? [])[0] ?? [];
      $raw = $relRecord[$item['name']] ?? null;
    } else {
      $raw = $record[$item['name']] ?? null;
    }
    if ($raw === null || $raw === '') return $isPreview ? '123 Example Street<br>12345 Berlin, Germany' : '—';

    // Stored as JSON string or already-decoded array
    if (is_string($raw)) {
      $decoded = json_decode($raw, true);
      $raw = is_array($decoded) ? $decoded : [];
    }
    if (!is_array($raw)) return '—';

    $street  = trim((string) ($raw['street']      ?? ''));
    $postal  = trim((string) ($raw['postal_code'] ?? ''));
    $city    = trim((string) ($raw['city']        ?? ''));
    $state   = trim((string) ($raw['state']       ?? ''));
    $country = trim((string) ($raw['country']     ?? ''));

    $line2 = implode(' ', array_filter([$postal, $city]));
    $line3 = implode(', ', array_filter([$state, $country]));
    $lines = array_filter([$street, $line2, $line3]);

    return $lines
      ? implode('<br>', array_map('e', $lines))
      : ($isPreview ? '123 Example Street<br>12345 Berlin, Germany' : '—');
  };

  // Mirrors StatusField.vue predefined status color schemes
  $statusStyleMap = [
    'success' => ['color' => '#065f46', 'bgColor' => '#d1fae5'],
    'warning' => ['color' => '#92400e', 'bgColor' => '#fed7aa'],
    'danger'  => ['color' => '#991b1b', 'bgColor' => '#fee2e2'],
    'info'    => ['color' => '#1e40af', 'bgColor' => '#bfdbfe'],
    'default' => ['color' => '#374151', 'bgColor' => '#e5e7eb'],
  ];

  // Render a status field as a colored pill badge, using the option's status/color/bgColor.
  $resolveStatusBadge = function (array $item) use ($record, $fieldMap, $relationshipData, $statusStyleMap, $isPreview): string {
    if ($isPreview) {
      return '<span style="display:block;padding:2px 10px;border-radius:15px;background:#fee2e2;color:#991b1b;font-size:14px;font-weight:bold;">'
        . e(__('globals.pdf.preview_overdue')) . '</span>';
    }
    $relName = $item['relationship'] ?? null;
    if ($relName) {
      $relRecord = ($relationshipData[$relName] ?? [])[0] ?? [];
      $rawValue  = $relRecord[$item['name']] ?? null;
    } else {
      $rawValue = $record[$item['name']] ?? null;
    }
    if ($rawValue === null || $rawValue === '') return '—';

    $field  = $fieldMap->get($item['name']);
    $opts   = $field?->dropdown_list?->values ?? [];
    $option = null;
    foreach ($opts as $entry) {
      if ((string)($entry['value'] ?? '') === (string) $rawValue) {
        $option = $entry;
        break;
      }
    }
    if (!$option) return e((string) $rawValue);

    $label = $option['label'] ?? (string) $rawValue;
    if (str_contains($label, '.')) $label = __($label);

    $key     = $option['status'] ?? 'default';
    $styles  = $statusStyleMap[$key] ?? $statusStyleMap['default'];
    $color   = $option['color']   ?? $styles['color'];
    $bgColor = $option['bgColor'] ?? $styles['bgColor'];

    return '<span style="display:block;padding:2px 10px;border-radius:15px;'
      . 'background:' . e($bgColor) . ';color:' . e($color) . ';'
      . 'font-size:14px;font-weight:bold;">'
      . e($label) . '</span>';
  };

  // Render a single header/footer/field slot item as HTML.
  // All user-supplied values are escaped with e(); only structural tags are raw.
  $renderSlot = function (?array $slot, string $align) use (
    $company, $companyInitials, $docTitle, $record, $resolveField, $isPreview, $resolveStatusBadge, $resolveAddress
  ): string {
    if (!$slot) return '';
    $kind = $slot['kind'] ?? 'field';
    $ta   = 'text-align:' . $align . ';';
$ta = $ta." display: flex;width: 100%;justify-content: flex-end; ";
    if ($kind === 'logo') {
      if (!empty($company['logo_url']))
        $logoHtml = '<img src="' . e($company['logo_url']) . '" class="hdr-logo-img" alt="" />';
      else
        $logoHtml = '<div class="hdr-logo-box" style="display:inline-block;">' . e($companyInitials) . '</div>';
      return '<div style="' . $ta . '">' . $logoHtml . '</div>';
    }
    if ($kind === 'meta') {
      $parts = [];
      if (!empty($company['address'])) $parts[] = e($company['address']);
      if (!empty($company['phone']))   $parts[] = e($company['phone']);
      if (!empty($company['email']))   $parts[] = e($company['email']);
      $html  = '<div class="hdr-co-name" style="' . $ta . '">' . e($company['name'] ?? '') . '</div>';
      if ($parts) $html .= '<div class="hdr-co-meta" style="' . $ta . '">' . implode('<br>', $parts) . '</div>';
      return $html;
    }
    if ($kind === 'title') {
      $html = '<div class="hdr-doc-title" style="' . $ta . '">' . e($docTitle) . '</div>';
      if (!empty($record['number']))
        $html .= '<div class="hdr-doc-num" style="' . $ta . '"># ' . e($record['number']) . '</div>';
      return $html;
    }
    if ($kind === 'field') {
      $lbl   = !empty($slot['label']) ? __($slot['label']) : ($slot['name'] ?? '');
      $val   = isset($slot['name']) ? $resolveField($slot['name']) : '—';
      $vs    = !empty($slot['displayStyle']) ? ' fval--' . $slot['displayStyle'] : '';
      $html  = '';
      if ($slot['showLabel'] ?? true)
        $html .= '<div class="hdr-meta-lbl" style="' . $ta . '">' . e($lbl) . '</div>';
      $ds = $slot['displayStyle'] ?? '';
      if ($ds === 'status') {
        $valHtml = $resolveStatusBadge($slot);
      } elseif ($ds === 'address') {
        $valHtml = $resolveAddress($slot);
      } else {
        $valHtml = e($val);
      }
      $html .= '<div class="hdr-meta-row' . $vs . '" style="' . $ta . '">' . $valHtml . '</div>';
      return $html;
    }
    if ($kind === 'page_number') {
      if ($isPreview)
        return '<span class="hdr-page-num pdf-pn-preview" style="' . $ta . '">1 / 1</span>';
      return '<span class="hdr-page-num" style="' . $ta . '"><span class="pdf-pn"></span>&nbsp;/&nbsp;<span class="pdf-pc"></span></span>';
    }
    if ($kind === 'date')
      return '<span class="hdr-date" style="' . $ta . '">' . e(now()->translatedFormat('M d, Y')) . '</span>';

    if ($kind === 'co_info_line') {
      $parts = array_filter([
        $company['name']    ?? null,
        $company['address'] ?? null,
        $company['phone']   ?? null,
        $company['email']   ?? null,
      ]);
      return '<div style="font-size:9px;color:#9ca3af;line-height:1.4;text-align:' . $align . ';">'
        . e(implode(' · ', $parts)) . '</div>';
    }

    return '';
  };

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
  <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
  <style>{!! $fontFaceCSS !!}</style>
  <style>
    * {
      margin: 0;
      padding: 0;
      box-sizing: border-box;
    }

    body {
      font-family: 'Fira Sans', 'Heebo', 'DejaVu Sans', sans-serif;
      font-size: 12px;
      color: #333;
      line-height: 1.4;
    }

    .page {
      padding: 40px 48px 56px;
    }

    /* ── Header ─────────────────────────────────────────────────── */
    .hdr {
      width: 100%;
      border-collapse: collapse;
      margin-bottom: 30px;
    }

    .hdr-logo-img {
      max-height: 52px;
      max-width: 160px;
      display: block;
      margin-bottom: 8px;
    }

    .hdr-logo-box {
      width: 44px;
      height: 44px;
      background: #4f46e5;
      border-radius: 8px;
      text-align: center;
      line-height: 44px;
      color: #fff;
      font-size: 14px;
      font-weight: bold;
      margin-bottom: 8px;
    }

    .hdr-co-name {
      font-size: 15px;
      font-weight: bold;
      color: #111;
      margin-bottom: 3px;
    }

    .hdr-co-meta {
      font-size: 10px;
      color: #666;
      line-height: 1.75;
    }

    .hdr-doc-title {
      font-size: 75px;
      font-weight: bold;
      color: #111;
      line-height: 1.1;
    }

    .hdr-doc-num {
      font-size: 12px;
      color: #888;
      margin-top: 5px;
    }

    .hdr-meta-row {
      font-size: 11px;
      color: #444;
      margin-top: 4px;
    }

    .hdr-meta-lbl {
      color: #999;
      font-size: 10px;
    }

    /* ── Section blocks ──────────────────────────────────────────── */
    .sec-label {
      font-size: 9px;
      font-weight: bold;
      text-transform: uppercase;
      letter-spacing: 0.7px;
      color: #888;
      margin-bottom: 7px;
    }

    .sec-sep {
      border: none;
      border-bottom: 1px solid #e5e7eb;
      margin-bottom: 12px;
      padding-bottom: 5px;
    }

    .sec-block {
      margin-bottom: 22px;
    }

    /* ── Fields: horizontal label-above-value (full-width) ──────── */
    .frow {
      width: 100%;
      border-collapse: collapse;
    }

    .fcell {
      vertical-align: top;
      padding-right: 20px;
    }

    .fcell-lbl {
      font-size: 9px;
      font-weight: bold;
      text-transform: uppercase;
      letter-spacing: 0.6px;
      color: #888;
      margin-bottom: 4px;
    }

    .fcell-val {
      font-size: 12px;
      font-weight: bold;
      color: #111;
    }

    /* ── Fields: stacked values (half-width) ────────────────────── */
    .fstack-val {
      font-size: 12px;
      font-weight: bold;
      color: #111;
      margin-bottom: 2px;
    }

    .fstack-val--sub {
      font-size: 11px;
      font-weight: normal;
      color: #444;
    }

    /* ── Text block ─────────────────────────────────────────────── */
    .txtbox {
      border-radius: 6px;
      padding: 12px 15px;
      margin-bottom: 22px;
    }

    .txtbox-title {
      font-size: 9px;
      font-weight: bold;
      text-transform: uppercase;
      letter-spacing: 0.7px;
      color: #888;
      margin-bottom: 7px;
    }

    .txtbox-body {
      font-size: 11px;
      color: #444;
      line-height: 1.75;
      white-space: pre-wrap;
    }

    /* ── Divider ─────────────────────────────────────────────────── */
    .divider {
      border: none;
      border-top: 1px solid #e5e7eb;
      margin: 14px 0;
    }

    /* ── Relationship / generic table ───────────────────────────── */
    .rtbl {
      width: 100%;
      border-collapse: collapse;
      margin-bottom: 4px;
    }

    .rtbl thead tr {
      border-top: 1.5px solid #111;
      border-bottom: 1.5px solid #111;
    }

    .rtbl thead th {
      font-size: 10px;
      font-weight: bold;
      padding: 6px 6px;
      text-align: left;
      color: #555;
      text-transform: uppercase;
      letter-spacing: 0.4px;
    }

    .rtbl tbody tr {
      border-bottom: 1px solid #e9ecef;
    }

    .rtbl tbody td {
      padding: 8px 6px;
      font-size: 11px;
      vertical-align: top;
    }

    /* ── Line items table ────────────────────────────────────────── */
    .litbl {
      width: 100%;
      border-collapse: collapse;
      margin-bottom: 2px;
    }

    .litbl thead tr {
      border-top: 1.5px solid #111;
      border-bottom: 1.5px solid #111;
    }

    .litbl thead th {
      font-size: 10px;
      font-weight: bold;
      padding: 7px 6px;
      text-align: left;
      color: #555;
      text-transform: uppercase;
      letter-spacing: 0.4px;
    }

    .litbl thead th.r {
      text-align: right;
    }

    .litbl tbody tr {
      border-bottom: 1px solid #e9ecef;
    }

    .litbl tbody td {
      padding: 9px 6px;
      font-size: 11px;
      vertical-align: top;
    }

    .litbl tbody td.r {
      text-align: right;
      white-space: nowrap;
    }

    .li-name {
      font-weight: bold;
      color: #111;
    }

    .li-note {
      font-size: 10px;
      color: #777;
      margin-top: 2px;
    }

    /* ── Totals ──────────────────────────────────────────────────── */
    .totals-wrap {
      width: 100%;
      border-collapse: collapse;
      margin: 8px 0 24px;
    }

    .ttbl {
      border-collapse: collapse;
      width: 100%;
    }

    .ttbl td {
      padding: 4px 0;
      font-size: 11px;
    }

    .t-lbl {
      color: #555;
      padding-right: 28px;
      text-align: left;
    }

    .t-val {
      text-align: right;
      font-weight: normal;
      color: #111;
      white-space: nowrap;
    }

    .t-neg {
      color: #ef4444;
    }

    .t-grand td {
      border-top: 1.5px solid #111;
      padding-top: 8px;
      font-size: 13px;
      font-weight: bold;
      color: #111;
    }

    /* ── Footer ──────────────────────────────────────────────────── */
    .footer-wrap {
      @if($isPreview)
      position: static;
      margin-top: 32px;
      padding: 0 48px 32px;
      @else
      position: fixed;
      bottom: 0;
      left: 0;
      right: 0;
      background: #fff;
      padding: 0 48px 14px;
      @endif
    }

    .footer {
      width: 100%;
      border-collapse: collapse;
      border-top: 1px solid #d1d5db;
    }

    .footer td {
      font-size: 9px;
      color: #999;
      vertical-align: top;
      padding-top: 8px;
      line-height: 1.65;
    }

    .footer--no-border {
      border-top: none;
    }

    /* ── Page counters (DomPDF counter() support) ─────────────── */
    .pdf-pn::after { content: counter(page); }
    .pdf-pc::after { content: counter(pages); }

    .hdr-page-num,
    .hdr-date {
      font-size: 11px;
      color: #555;
      display: block;
    }

    /* ── Footer: scale down large header-style elements ──────── */
    .footer-wrap .hdr-logo-img {
      max-height: 26px;
      max-width: 72px;
      margin-bottom: 0;
    }
    .footer-wrap .hdr-logo-box {
      width: 22px;
      height: 22px;
      line-height: 22px;
      font-size: 8px;
      margin-bottom: 0;
    }
    .footer-wrap .hdr-doc-title {
      font-size: 33px;
      font-weight: bold;
    }
    .footer-wrap .hdr-doc-num { font-size: 9px; }
    .footer-wrap .hdr-co-name { font-size: 9px; margin-bottom: 0; }
    .footer-wrap .hdr-co-meta { font-size: 8px; }
    .footer-wrap .hdr-page-num,
    .footer-wrap .hdr-date     { font-size: 9px; color: #999; }
    .footer-wrap .hdr-meta-row { font-size: 9px; color: #999; margin-top: 2px; }
    .footer-wrap .hdr-meta-lbl { font-size: 8px; }

    /* ── Field-section building-block cells ──────────────────── */
    .fcell-block {
      vertical-align: top;
      padding-right: 20px;
    }
    .fcell-block .hdr-logo-img {
      max-height: 36px;
      max-width: 100px;
      margin-bottom: 0;
    }
    .fcell-block .hdr-logo-box {
      width: 30px;
      height: 30px;
      line-height: 30px;
      font-size: 10px;
      margin-bottom: 0;
    }
    .fcell-block .hdr-doc-title { font-size: 45px; }
    .fcell-block .hdr-co-name   { font-size: 33px; }

    /* ── Field display-style variants ───────────────────────────
       Applied to .fcell-val, .fstack-val and .hdr-meta-row when
       a displayStyle attribute is set on a field item.
    ──────────────────────────────────────────────────────────── */
    .fval--title {
      font-size: 33px;
      font-weight: bold;
      color: #111;
      line-height: 1.2;
    }
    .fval--subtitle {
      font-size: 14px;
      font-weight: bold;
      color: #222;
    }
    .fval--bold {
      font-weight: bold;
      color: #111;
    }
    .fval--small {
      font-size: 9px;
      color: #9ca3af;
    }
    .fval--label {
      font-size: 8px;
      font-weight: bold;
      text-transform: uppercase;
      letter-spacing: 0.9px;
      color: #9ca3af;
    }
    .fval--status {
      display: inline-block;
      padding: 2px 10px;
      border-radius: 99px;
      font-size: 10px;
      font-weight: bold;
      color: #374151;
    }
    .fval--address {
      white-space: pre-wrap;
      line-height: 1.75;
      font-size: 11px;
      font-weight: normal;
    }
    .fval--highlight {
      display: inline-block;
      background: #fef9c3;
      padding: 1px 5px;
      border-radius: 3px;
    }
    .fval--muted {
      color: #9ca3af;
      font-size: 11px;
    }
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
        <table style="width:100%; border-collapse:collapse; margin-bottom:22px;">
          <tr>

            {{-- Left half --}}
            <td style="width:50%; vertical-align:top; padding-right:16px;">
              @php $sec = $secA;
              $secType = $sec['type'] ?? ''; @endphp
              @if($secType === 'fields')
                @php $fItems = array_values($sec['items'] ?? []); @endphp
                @if(count($fItems))
                  <div class="sec-block">
                    @if(!empty($sec['name']))
                    <div class="sec-label">{{ __($sec['name']) }}</div>@endif
                    @foreach($fItems as $fItem)
                      @if(($fItem['kind'] ?? 'field') === 'field')
                        @if($fItem['showLabel'] ?? true)
                          <div class="fcell-lbl" style="margin-bottom:2px;">
                          {{ $fItem['label'] ? __($fItem['label']) : $fItem['name'] }}</div>
                        @endif
                        @php
                          $ds   = $fItem['displayStyle'] ?? '';
                          $vs   = $ds ? ' fval--'.$ds : '';
                          if ($ds === 'status')       $fOut = $resolveStatusBadge($fItem);
                          elseif ($ds === 'address')  $fOut = $resolveAddress($fItem);
                          else                        $fOut = e($resolveItem($fItem));
                        @endphp
                        <div class="fstack-val{{ $vs }}" style="margin-bottom:8px;">{!! $fOut !!}</div>
                      @else
                        <div style="margin-bottom:8px;">{!! $renderSlot($fItem, 'left') !!}</div>
                      @endif
                    @endforeach
                  </div>
                @endif
              @elseif($secType === 'relationship')
                @php $relRecs = $relationshipData[$sec['relationship'] ?? ''] ?? [];
                $relCols = $sec['columns'] ?? []; @endphp
                @if(!empty($relCols) && !empty($relRecs))
                  <div class="sec-block">
                    @if(!empty($sec['label']))
                    <div class="sec-label">{{ __($sec['label']) }}</div>@endif
                    <table class="rtbl">
                      <thead>
                        <tr>@foreach($relCols as $c)<th>{{ $c['label'] ? __($c['label']) : $c['name'] }}</th>@endforeach</tr>
                      </thead>
                      <tbody>@foreach($relRecs as $rr)<tr>@foreach($relCols as $c)<td>{{ $rr[$c['name']] ?? '—' }}</td>
                      @endforeach</tr>@endforeach</tbody>
                    </table>
                  </div>
                @endif
              @endif
            </td>

            {{-- Right half --}}
            <td style="width:50%; vertical-align:top;">
              @php $sec = $secB;
              $secType = $sec['type'] ?? ''; @endphp
              @if($secType === 'fields')
                @php $fItems = array_values($sec['items'] ?? []); @endphp
                @if(count($fItems))
                  <div class="sec-block">
                    @if(!empty($sec['name']))
                    <div class="sec-label">{{ __($sec['name']) }}</div>@endif
                    @foreach($fItems as $fItem)
                      @if(($fItem['kind'] ?? 'field') === 'field')
                        @if($fItem['showLabel'] ?? true)
                          <div class="fcell-lbl" style="margin-bottom:2px;">
                          {{ $fItem['label'] ? __($fItem['label']) : $fItem['name'] }}</div>
                        @endif
                        @php
                          $ds   = $fItem['displayStyle'] ?? '';
                          $vs   = $ds ? ' fval--'.$ds : '';
                          if ($ds === 'status')       $fOut = $resolveStatusBadge($fItem);
                          elseif ($ds === 'address')  $fOut = $resolveAddress($fItem);
                          else                        $fOut = e($resolveItem($fItem));
                        @endphp
                        <div class="fstack-val{{ $vs }}" style="margin-bottom:8px;">{!! $fOut !!}</div>
                      @else
                        <div style="margin-bottom:8px;">{!! $renderSlot($fItem, 'left') !!}</div>
                      @endif
                    @endforeach
                  </div>
                @endif
              @elseif($secType === 'relationship')
                @php $relRecs = $relationshipData[$sec['relationship'] ?? ''] ?? [];
                $relCols = $sec['columns'] ?? []; @endphp
                @if(!empty($relCols) && !empty($relRecs))
                  <div class="sec-block">
                    @if(!empty($sec['label']))
                    <div class="sec-label">{{ __($sec['label']) }}</div>@endif
                    <table class="rtbl">
                      <thead>
                        <tr>@foreach($relCols as $c)<th>{{ $c['label'] ? __($c['label']) : $c['name'] }}</th>@endforeach</tr>
                      </thead>
                      <tbody>@foreach($relRecs as $rr)<tr>@foreach($relCols as $c)<td>{{ $rr[$c['name']] ?? '—' }}</td>
                      @endforeach</tr>@endforeach</tbody>
                    </table>
                  </div>
                @endif
              @endif
            </td>

          </tr>
        </table>

        {{-- ═══════════════════════════════════════════════════════════
        Single full-width section
        ════════════════════════════════════════════════════════════════ --}}
      @else
        @php $section = $rowSections[0];
        $type = $section['type'] ?? ''; @endphp

        {{-- ── Header ─────────────────────────────────────────────── --}}
        @if($type === 'header')
          @if(isset($section['rows']))
            @foreach($section['rows'] as $hRow)
              <table class="hdr">
                <tr>
                  <td style="width:50%; vertical-align:top; text-align:left;">
                    {!! $renderSlot($hRow['left'] ?? null, 'left') !!}
                  </td>
                  <td style="width:50%; vertical-align:top; text-align:right;">
                    {!! $renderSlot($hRow['right'] ?? null, 'right') !!}
                  </td>
                </tr>
              </table>
            @endforeach

          @endif

          {{-- ── Footer ─────────────────────────────────────────────── --}}
        @elseif($type === 'footer')
          <div class="footer-wrap">
            @if(isset($section['rows']))
              @foreach($section['rows'] as $fIdx => $fRow)
                <table class="footer {{ $fIdx > 0 ? 'footer--no-border' : '' }}">
                  <tr>
                    <td style="width:50%; vertical-align:top; text-align:left;">
                      {!! $renderSlot($fRow['left'] ?? null, 'left') !!}
                    </td>
                    <td style="width:50%; vertical-align:top; text-align:right;">
                      {!! $renderSlot($fRow['right'] ?? null, 'right') !!}
                    </td>
                  </tr>
                </table>
              @endforeach
            @else
              {{-- Legacy format: company info left, generated date right --}}
              <table class="footer">
                <tr>
                  <td style="width:65%;">
                    {{ $company['name'] ?? '' }}
                    @if(!empty($company['address'])) · {{ $company['address'] }}@endif
                    @if(!empty($company['website'])) · {{ $company['website'] }}@endif
                  </td>
                  <td style="width:35%; text-align:right;">
                    {{ __('globals.pdf.generated_on', ['date' => now()->translatedFormat('M d, Y')]) }}
                  </td>
                </tr>
              </table>
            @endif
          </div>

          {{-- ── Fields (full-width: horizontal label-above-value) ──── --}}
        @elseif($type === 'fields')
          @php $fItems = array_values($section['items'] ?? []); @endphp
          @if(count($fItems))
            <div class="sec-block">
              @if(!empty($section['name']))
                <div class="sec-label sec-sep">{{ __($section['name']) }}</div>
              @endif
              <table class="frow">
                <tr>
                  @foreach($fItems as $fItem)
                    @if(($fItem['kind'] ?? 'field') === 'field')
                      <td class="fcell">
                        @if($fItem['showLabel'] ?? true)
                          <div class="fcell-lbl">{{ $fItem['label'] ? __($fItem['label']) : $fItem['name'] }}</div>
                        @endif
                        @php
                          $ds   = $fItem['displayStyle'] ?? '';
                          $vs   = $ds ? ' fval--'.$ds : '';
                          if ($ds === 'status')       $fOut = $resolveStatusBadge($fItem);
                          elseif ($ds === 'address')  $fOut = $resolveAddress($fItem);
                          else                        $fOut = e($resolveItem($fItem));
                        @endphp
                        <div class="fcell-val{{ $vs }}">{!! $fOut !!}</div>
                      </td>
                    @else
                      <td class="fcell-block">{!! $renderSlot($fItem, 'left') !!}</td>
                    @endif
                  @endforeach
                </tr>
              </table>
            </div>
          @endif

          {{-- ── Text block ──────────────────────────────────────────── --}}
        @elseif($type === 'text')
          @if(!empty($section['content']))
            <div class="txtbox">
              @if(!empty($section['name']))
              <div class="txtbox-title">{{ __($section['name']) }}</div>@endif
              <div class="txtbox-body">{{ $section['content'] }}</div>
            </div>
          @endif

          {{-- ── Divider ─────────────────────────────────────────────── --}}
        @elseif($type === 'divider')
          <hr class="divider">

          {{-- ── Relationship (full-width) ──────────────────────────── --}}
        @elseif($type === 'relationship')
          @php
            $relName = $section['relationship'] ?? '';
            $relCols = $section['columns'] ?? [];
            $relRecords = $relationshipData[$relName] ?? [];
          @endphp
          @if(!empty($relCols) && !empty($relRecords))
            <div class="sec-block">
              @if(!empty($section['label']))
              <div class="sec-label">{{ __($section['label']) }}</div>@endif
              <table class="rtbl">
                <thead>
                  <tr>
                    @foreach($relCols as $col)<th>{{ $col['label'] ? __($col['label']) : $col['name'] }}</th>@endforeach
                  </tr>
                </thead>
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
              $liSubtotal += (float) ($li['subtotal'] ?? 0);
              $liDiscount += (float) ($li['discount_amount'] ?? 0);
              $liTax += (float) ($li['tax_amount'] ?? 0);
              $liTotal += (float) ($li['total'] ?? 0);
            }
            $liCols = $section['columns'] ?? [];   // optional middle columns
          @endphp
          @if(count($lineItems))
            <div class="sec-block">
              <table class="litbl">
                <thead>
                  <tr>
                    <th style="width:4%;">#</th>
                    <th>{{ __('globals.pdf.name') }}</th>
                    @foreach($liCols as $liCol)
                      <th class="r">{{ $liCol['label'] ? __($liCol['label']) : $liCol['name'] }}</th>
                    @endforeach
                    <th class="r" style="width:11%;">{{ __('globals.pdf.total') }}</th>
                  </tr>
                </thead>
                <tbody>
                  @foreach($lineItems as $liIdx => $liItem)
                    <tr>
                      <td>{{ $liIdx + 1 }}</td>
                      <td>
                        <div class="li-name">{{ $liItem['name'] ?? '' }}</div>
                        @if(!empty($liItem['note']))
                        <div class="li-note">{{ $liItem['note'] }}</div>@endif
                      </td>
                      @foreach($liCols as $liCol)
                        @php
                          $raw     = $liItem[$liCol['name']] ?? '';
                          $colType = $liCol['type'] ?? '';
                          if ($raw === '') {
                            $fmt = '—';
                          } elseif ($colType === 'select') {
                            $liField = $lineItemFieldMap->get($liCol['name']);
                            $fmt = $renderer->render('select', (string) $raw, $liField?->dropdown_list?->values ?? null);
                          } elseif (is_numeric($raw) && $colType === 'percentage') {
                            $fmt = \App\Support\PdfNumberFormatter::format((float) $raw) . '%';
                          } elseif (is_numeric($raw)) {
                            $fmt = \App\Support\PdfNumberFormatter::format((float) $raw);
                          } else {
                            $fmt = (string) $raw;
                          }
                        @endphp
                        <td class="r">{{ $fmt }}</td>
                      @endforeach
                      <td class="r">{{ \App\Support\PdfNumberFormatter::format((float) ($liItem['total'] ?? 0)) }} {{ $currencySymbol }}</td>
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
                        <td class="t-lbl">{{ __('globals.pdf.subtotal') }}</td>
                        <td class="t-val">{{ \App\Support\PdfNumberFormatter::format($liSubtotal) }} {{ $currencySymbol }}</td>
                      </tr>
                      @if($liTax > 0)
                        <tr>
                          <td class="t-lbl">{{ __('globals.pdf.tax_amount') }}</td>
                          <td class="t-val">{{ \App\Support\PdfNumberFormatter::format($liTax) }} {{ $currencySymbol }}</td>
                        </tr>
                      @endif
                      @if($liDiscount > 0)
                        <tr>
                          <td class="t-lbl">{{ __('globals.pdf.discount_amount') }}</td>
                          <td class="t-val t-neg">−{{ \App\Support\PdfNumberFormatter::format($liDiscount) }} {{ $currencySymbol }}</td>
                        </tr>
                      @endif
                      <tr class="t-grand">
                        <td class="t-lbl">{{ __('globals.pdf.total') }}</td>
                        <td class="t-val">{{ \App\Support\PdfNumberFormatter::format($liTotal) }} {{ $currencySymbol }}</td>
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

  @if($isPreview)
  <script>
    window.addEventListener('load', function () {
      var h = Math.max(document.documentElement.scrollHeight, document.body.scrollHeight);
      var pages = Math.max(1, Math.ceil(h / 1122));
      document.querySelectorAll('.pdf-pn-preview').forEach(function (el) {
        el.textContent = '1 / ' + pages;
      });
    });
  </script>
  @endif
</body>

</html>