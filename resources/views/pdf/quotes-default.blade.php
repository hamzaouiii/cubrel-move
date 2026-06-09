<!DOCTYPE html>
<html lang="en">
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: DejaVu Sans, sans-serif; font-size: 13px; color: #333; }
        .page { padding: 48px; }

        .header-table { width: 100%; border-collapse: collapse; margin-bottom: 32px; }
        .app-name { font-size: 18px; font-weight: bold; color: #111; }
        .doc-title { font-size: 24px; font-weight: bold; color: #111; text-align: right; }

        .divider { border: none; border-top: 2px solid #e5e7eb; margin: 20px 0; }

        .record-name { font-size: 20px; font-weight: bold; color: #111; margin-bottom: 24px; }

        .fields-table { width: 100%; border-collapse: collapse; }
        .fields-table td { padding: 8px 4px; vertical-align: top; }
        .field-label { width: 35%; font-size: 10px; text-transform: uppercase;
                       letter-spacing: 0.8px; color: #9ca3af; padding-right: 16px; }
        .field-value { color: #111; font-size: 13px; }
        .fields-table tr { border-bottom: 1px solid #f3f4f6; }

        .footer { margin-top: 40px; padding-top: 14px; border-top: 1px solid #e5e7eb;
                  text-align: center; color: #9ca3af; font-size: 10px; }
    </style>
</head>
<body>
<div class="page">

    <table class="header-table">
        <tr>
            <td style="width:55%; vertical-align:top;">
                <div class="app-name">{{ config('app.name') }}</div>
            </td>
            <td style="width:45%; vertical-align:top;">
                <div class="doc-title">{{ strtoupper($moduleLabel ?? $module) }}</div>
            </td>
        </tr>
    </table>

    <div class="record-name">{{ $record['name'] ?? ('Record #' . $record['id']) }}</div>

    <hr class="divider">

    <table class="fields-table">
        @foreach ($fields as $field)
            @php
                $key   = $field['name'] ?? $field['key'] ?? null;
                $label = $field['label'] ?? $key;
                $value = $record[$key] ?? null;
                if ($value === null || $value === '' || in_array($key, ['id', 'custom_fields', 'related'])) continue;
            @endphp
            <tr>
                <td class="field-label">{{ $label }}</td>
                <td class="field-value">
                    @if(is_array($value))
                        {{ implode(', ', $value) }}
                    @else
                        {{ $value }}
                    @endif
                </td>
            </tr>
        @endforeach
    </table>

    <div class="footer">
        Generated on {{ now()->format('M d, Y') }} &bull; {{ config('app.name') }}
    </div>

</div>
</body>
</html>
