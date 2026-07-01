<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
    <title>{{ __('emails.setup.title', ['app' => $appName]) }}</title>
</head>
<body style="margin:0;padding:0;background-color:#f3f4f6;font-family:Arial,'Helvetica Neue',Helvetica,sans-serif;color:#1f2937;">

    {{-- Outer wrapper --}}
    <table width="100%" cellpadding="0" cellspacing="0" border="0" role="presentation"
           style="background-color:#f3f4f6;padding:40px 0;">
        <tr>
            <td align="center">

                {{-- Card container --}}
                <table width="100%" cellpadding="0" cellspacing="0" border="0" role="presentation"
                       style="max-width:600px;width:100%;margin:0 auto;">

                    {{-- Logo header --}}
                    <tr>
                        <td align="center" style="padding:0 24px 24px;">
                            <a href="{{ $appUrl }}" target="_blank" style="text-decoration:none;display:inline-block;">
                                <img src="{{ $message->embed(public_path('img/Cubrel-logo/default.png')) }}"
                                     alt="{{ $appName }}"
                                     width="140"
                                     style="display:block;border:0;outline:none;text-decoration:none;" />
                            </a>
                        </td>
                    </tr>

                    {{-- Card --}}
                    <tr>
                        <td style="padding:0 24px 24px;">
                            <table width="100%" cellpadding="0" cellspacing="0" border="0" role="presentation"
                                   style="background-color:#ffffff;border-radius:16px;overflow:hidden;box-shadow:0 4px 24px rgba(0,0,0,0.08);">

                                {{-- Accent bar --}}
                                <tr>
                                    <td height="5" style="background-color:{{ $primaryColor }};font-size:0;line-height:0;">&nbsp;</td>
                                </tr>

                                {{-- Card body --}}
                                <tr>
                                    <td style="padding:40px 40px 32px;">

                                        {{-- Icon badge --}}
                                        <table cellpadding="0" cellspacing="0" border="0" role="presentation"
                                               style="margin:0 auto 28px;">
                                            <tr>
                                                <td align="center"
                                                    style="width:56px;height:56px;border-radius:14px;background-color:{{ $primaryColor }}1a;">
                                                    <img src="{{ $message->embed(public_path('android-chrome-192x192.png')) }}" alt="" width="32" height="32"
                                                         style="display:block;margin:12px auto;border:0;outline:none;text-decoration:none;" />
                                                </td>
                                            </tr>
                                        </table>

                                        {{-- Heading --}}
                                        <p style="margin:0 0 8px;font-size:22px;font-weight:700;color:#111827;text-align:center;letter-spacing:-0.02em;">
                                            {{ __('emails.setup.heading' , ['app' => $appName]) }}
                                        </p>

                                        {{-- Subheading --}}
                                        <p style="margin:0 0 32px;font-size:15px;color:#6b7280;text-align:center;line-height:1.6;">
                                            {{ __('emails.setup.body', ['app' => $appName]) }}
                                        </p>

                                        {{-- CTA button --}}
                                        <table width="100%" cellpadding="0" cellspacing="0" border="0" role="presentation"
                                               style="margin-bottom:32px;">
                                            <tr>
                                                <td align="center">
                                                    <a href="{{ $setupUrl }}" target="_blank"
                                                       style="display:inline-block;background-color:{{ $primaryColor }};color:#ffffff;font-size:15px;font-weight:600;text-decoration:none;padding:14px 32px;border-radius:10px;letter-spacing:0.01em;">
                                                        {{ __('emails.setup.cta') }}
                                                    </a>
                                                </td>
                                            </tr>
                                        </table>

                                        {{-- Divider --}}
                                        <table width="100%" cellpadding="0" cellspacing="0" border="0" role="presentation"
                                               style="margin-bottom:24px;">
                                            <tr>
                                                <td style="border-top:1px solid #e5e7eb;font-size:0;line-height:0;">&nbsp;</td>
                                            </tr>
                                        </table>

                                        {{-- Expiry notice --}}
                                        <p style="margin:0 0 16px;font-size:13px;color:#6b7280;text-align:center;line-height:1.5;">
                                            ⏱ {{ __('emails.setup.expires', ['date' => $expiresAt]) }}
                                        </p>

                                        {{-- Fallback URL --}}
                                        <p style="margin:0;font-size:12px;color:#9ca3af;text-align:center;line-height:1.5;">
                                            {{ __('emails.setup.fallback') }}<br />
                                            <a href="{{ $setupUrl }}" style="color:{{ $primaryColor }};word-break:break-all;">{{ $setupUrl }}</a>
                                        </p>

                                    </td>
                                </tr>

                                {{-- Legal footer inside card --}}
                                <tr>
                                    <td style="padding:20px 40px;background-color:#f9fafb;border-top:1px solid #e5e7eb;">
                                        <p style="margin:0;font-size:12px;color:#9ca3af;text-align:center;line-height:1.5;">
                                            {{ __('emails.setup.disclaimer') }}
                                        </p>
                                    </td>
                                </tr>

                            </table>
                        </td>
                    </tr>

                    {{-- Outer footer --}}
                    <tr>
                        <td align="center" style="padding:0 24px 40px;">
                            <p style="margin:0;font-size:12px;color:#9ca3af;line-height:1.5;">
                                © {{ date('Y') }}
                                <a href="{{ $appUrl }}" style="color:#9ca3af;text-decoration:none;">{{ $appName }}</a>.
                                {{ __('emails.common.all_rights_reserved') }}
                            </p>
                        </td>
                    </tr>

                </table>

            </td>
        </tr>
    </table>

</body>
</html>
