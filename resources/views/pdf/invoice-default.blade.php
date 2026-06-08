@php
    $renderer ??= app(\App\Support\PdfValueRenderer::class);
    $fieldMap = collect($fields)->keyBy('name');

    $resolveField = function (string $key) use ($record, $fieldMap, $renderer): string {
        $value = $record[$key] ?? null;
        if ($value === null || $value === '') return '—';

        $field = $fieldMap->get($key);
        if (!$field) return (string) $value;

        $dropdownValues = $field->dropdown_list?->values ?? null;
        return $renderer->render($field->type, $value, $dropdownValues);
    };
@endphp
<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}">
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: DejaVu Sans, sans-serif; font-size: 12px; color: #333; }
        .page { padding: 40px 48px 32px 48px; }

        /* ── Sender line (small, above recipient block) ── */
        .sender-line {
            font-size: 8.5px;
            color: #cc4400;
            margin-bottom: 10px;
            border-bottom: 1px solid #cc4400;
            padding-bottom: 2px;
            display: inline-block;
        }

        /* ── Top layout: recipient left, logo right ── */
        .top-table { width: 100%; border-collapse: collapse; margin-bottom: 24px; }
        .recipient-block { font-size: 12px; color: #111; line-height: 1.8; }
        .recipient-block strong { font-size: 13px; }

        /* ── Meta row: Kundennummer / Liefer- /Rechnungsdatum ── */
        .meta-row-table {
            width: 55%;
            border-collapse: collapse;
            margin: 0 auto 28px auto;
        }
        .meta-row-table td {
            text-align: center;
            padding: 0 12px;
            border-left: 1px solid #e5e7eb;
        }
        .meta-row-table td:first-child { border-left: none; }
        .meta-row-label { font-size: 10px; font-weight: bold; color: #111; }
        .meta-row-value { font-size: 11px; color: #444; margin-top: 2px; }

        /* ── Invoice heading ── */
        .invoice-heading {
            font-size: 26px;
            font-weight: bold;
            color: #111;
            margin-bottom: 14px;
        }

        /* ── Intro text ── */
        .intro-text { font-size: 12px; color: #333; line-height: 1.6; margin-bottom: 20px; }

        /* ── Line items table ── */
        .items-table { width: 100%; border-collapse: collapse; margin-bottom: 24px; }
        .items-table thead tr {
            border-top: 2px solid #111;
            border-bottom: 2px solid #111;
        }
        .items-table thead th {
            font-size: 11px;
            font-weight: bold;
            padding: 7px 8px;
            text-align: left;
            color: #111;
        }
        .items-table thead th.align-right { text-align: right; }
        .items-table tbody tr { border-bottom: 1px solid #e5e7eb; }
        .items-table tbody td { padding: 9px 8px; font-size: 12px; vertical-align: top; }
        .items-table tbody td.align-right { text-align: right; }
        .items-table .item-name { font-weight: bold; }
        .items-table .item-sub { font-size: 11px; color: #555; margin-top: 2px; }

        /* ── Totals block (right-aligned) ── */
        .totals-wrapper { width: 100%; }
        .totals-table { margin-left: auto; border-collapse: collapse; min-width: 240px; }
        .totals-table td { padding: 5px 10px; font-size: 12px; }
        .totals-table .t-label { color: #333; }
        .totals-table .t-amount { text-align: right; white-space: nowrap; }
        .totals-table .row-divider td { border-top: 1px solid #ccc; }
        .totals-table .row-total td {
            border-top: 2px solid #111;
            font-size: 14px;
            font-weight: bold;
            padding-top: 8px;
        }

        /* ── Payment note ── */
        .payment-note { margin-top: 32px; font-size: 12px; color: #333; line-height: 1.6; }
        .signature { margin-top: 24px; font-size: 13px; color: #333; }

        /* ── Footer ── */
        .footer-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 48px;
            border-top: 1px solid #ccc;
            padding-top: 10px;
        }
        .footer-table td {
            font-size: 9.5px;
            color: #555;
            vertical-align: top;
            padding: 8px 10px 0 0;
            line-height: 1.6;
        }
        .footer-table td:last-child { text-align: right; padding-right: 0; }
    </style>
</head>
<body>
<div class="page">

    {{-- ── Top: sender line / recipient block + logo ────────────────────────── --}}
    <table class="top-table">
        <tr>
            <td style="width:55%; vertical-align:top; padding-top:30px;">
                {{-- Small sender address above recipient --}}
                <div class="sender-line">
                    {{ $company['name'] }}
                    @if(!empty($company['address'])) | {{ $company['address'] }}@endif
                </div>

                {{-- Recipient block --}}
                <div class="recipient-block">
                    @if(!empty($record['billing_account_name']))
                        <strong>{{ $record['billing_account_name'] }}</strong><br>
                    @endif
                    @if(!empty($record['billing_contact_name']))
                        {{ $record['billing_contact_name'] }}<br>
                    @endif
                    @if(!empty($record['billing_address_street']))
                        {{ $record['billing_address_street'] }}<br>
                    @endif
                    @if(!empty($record['billing_address_city']))
                        {{ $record['billing_address_postalcode'] ?? '' }} {{ $record['billing_address_city'] }}
                    @endif
                </div>
            </td>
            <td style="width:45%; vertical-align:top; text-align:right;">
                @if(!empty($company['logo_url']))
                    <img src="{{ $company['logo_url'] }}"
                         style="max-height:70px; max-width:200px;" />
                @else
                    {{-- Placeholder box matching Lexware-style orange logo area --}}
                    <div style="display:inline-block; background:#f97316; color:#fff;
                                font-size:16px; font-weight:bold; padding:14px 28px;
                                border-radius:4px; letter-spacing:1px;">
                        {{ __('pdf.your_logo') }}
                    </div>
                @endif
            </td>
        </tr>
    </table>

    {{-- ── Meta row: Kundennummer / Liefer- / Rechnungsdatum ─────────────────── --}}
    <table class="meta-row-table">
        <tr>
            @if(!empty($record['account_id']))
            <td>
                <div class="meta-row-label">{{ __('modules.invoices.fields.customer_number') }}</div>
                <div class="meta-row-value">{{ $record['account_id'] }}</div>
            </td>
            @endif
            @if(!empty($record['issue_date']))
            <td>
                <div class="meta-row-label">{{ __('modules.invoices.fields.service_date') }}</div>
                <div class="meta-row-value">{{ $resolveField('issue_date') }}</div>
            </td>
            @endif
            @if(!empty($record['due_date']))
            <td>
                <div class="meta-row-label">{{ __('modules.invoices.fields.due_date') }}</div>
                <div class="meta-row-value">{{ $resolveField('due_date') }}</div>
            </td>
            @endif
        </tr>
    </table>

    {{-- ── Invoice heading ─────────────────────────────────────────────────── --}}
    <div class="invoice-heading">
        {{ __('pdf.invoice') }}
        @if(!empty($record['number']))Nr. {{ $record['number'] }}@endif
    </div>

    {{-- ── Intro / salutation text ─────────────────────────────────────────── --}}
    @if(!empty($record['intro_text']))
        <div class="intro-text">{{ $record['intro_text'] }}</div>
    @else
        <div class="intro-text">{{ __('pdf.invoice_intro') }}</div>
    @endif

    {{-- ── Line items table ────────────────────────────────────────────────── --}}
    @if(!empty($lineItems) && count($lineItems) > 0)
    <table class="items-table">
        <thead>
            <tr>
                <th style="width:6%;">{{ __('pdf.pos') }}</th>
                <th style="width:46%;">{{ __('pdf.description') }}</th>
                <th style="width:16%; text-align:right;">{{ __('pdf.quantity') }}</th>
                <th style="width:16%;" class="align-right">{{ __('pdf.unit_price') }} ({{ $record['currency'] ?? '€' }})</th>
                <th style="width:16%;" class="align-right">{{ __('pdf.total') }} ({{ $record['currency'] ?? '€' }})</th>
            </tr>
        </thead>
        <tbody>
            @foreach($lineItems as $index => $item)
            <tr>
                <td>{{ $index + 1 }}</td>
                <td>
                    <div class="item-name">{{ $item['name'] ?? '' }}</div>
                    @if(!empty($item['description']))
                        <div class="item-sub">{{ $item['description'] }}</div>
                    @endif
                </td>
                <td class="align-right">
                    {{ $item['quantity'] ?? '' }}
                    @if(!empty($item['unit'])) {{ $item['unit'] }}@endif
                </td>
                <td class="align-right">{{ number_format((float)($item['unit_price'] ?? 0), 2, ',', '.') }}</td>
                <td class="align-right">{{ number_format((float)($item['total'] ?? 0), 2, ',', '.') }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>
    @endif

    {{-- ── Totals ───────────────────────────────────────────────────────────── --}}
    <div class="totals-wrapper">
        <table class="totals-table">
            @if(isset($record['subtotal']) && $record['subtotal'] !== null)
            <tr class="row-divider">
                <td class="t-label">{{ __('modules.invoices.fields.subtotal') }}</td>
                <td class="t-amount">{{ $resolveField('subtotal') }} {{ $record['currency'] ?? '€' }}</td>
            </tr>
            @endif
            @if(isset($record['tax']) && $record['tax'] !== null)
            <tr class="row-divider">
                <td class="t-label">
                    {{ __('modules.invoices.fields.tax') }}
                    @if(!empty($record['tax_rate'])) {{ $record['tax_rate'] }}%@endif
                </td>
                <td class="t-amount">{{ $resolveField('tax') }} {{ $record['currency'] ?? '€' }}</td>
            </tr>
            @endif
            @if(isset($record['total']) && $record['total'] !== null)
            <tr class="row-total">
                <td class="t-label">{{ __('modules.invoices.fields.total') }}</td>
                <td class="t-amount">{{ $resolveField('total') }} {{ $record['currency'] ?? '€' }}</td>
            </tr>
            @endif
        </table>
    </div>

    {{-- ── Payment note ─────────────────────────────────────────────────────── --}}
    <div class="payment-note">
        @if(!empty($record['payment_terms']))
            {{ $record['payment_terms'] }}
        @else
            {{ __('pdf.payment_terms_default') }}
        @endif
    </div>

    {{-- ── Notes ───────────────────────────────────────────────────────────── --}}
    @if(!empty($record['notes']))
    <div style="margin-top:20px; font-size:12px; color:#333; line-height:1.6;">
        {{ $record['notes'] }}
    </div>
    @endif

    {{-- ── Signature / closing ─────────────────────────────────────────────── --}}
    <div class="signature">
        <div style="margin-bottom:6px;">{{ __('pdf.regards') }}</div>
        @if(!empty($company['signatory']))
            <div style="font-style:italic; color:#cc4400; font-size:15px; margin-top:4px;">
                {{ $company['signatory'] }}
            </div>
        @endif
    </div>

    {{-- ── Footer ──────────────────────────────────────────────────────────── --}}
    <table class="footer-table">
        <tr>
            <td style="width:25%;">
                <strong>{{ $company['name'] }}</strong><br>
                @if(!empty($company['address'])){{ $company['address'] }}<br>@endif
                @if(!empty($company['vat_id'])){{ __('pdf.vat_id') }}: {{ $company['vat_id'] }}@endif
            </td>
            <td style="width:25%;">
                @if(!empty($company['phone'])){{ __('pdf.tel') }}: {{ $company['phone'] }}<br>@endif
                @if(!empty($company['fax'])){{ __('pdf.fax') }}: {{ $company['fax'] }}<br>@endif
                @if(!empty($company['email'])){{ __('pdf.email') }}: {{ $company['email'] }}<br>@endif
                @if(!empty($company['website'])){{ __('pdf.web') }}: {{ $company['website'] }}@endif
            </td>
            <td style="width:30%;">
                @if(!empty($company['bank_name'])){{ $company['bank_name'] }}<br>@endif
                @if(!empty($company['iban']))IBAN: {{ $company['iban'] }}<br>@endif
                @if(!empty($company['bic']))BIC: {{ $company['bic'] }}<br>@endif
                @if(!empty($company['account_holder'])){{ __('pdf.account_holder') }}: {{ $company['account_holder'] }}@endif
            </td>
            <td style="width:20%; vertical-align:middle;">
                {{ __('pdf.generated_on', ['date' => now()->translatedFormat('M d, Y')]) }}
            </td>
        </tr>
    </table>

</div>
</body>
</html>