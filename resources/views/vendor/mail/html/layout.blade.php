<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN"
  "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
    <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8"/>
    <title>{{ $title ?? '' }}</title>
</head>
<body style="margin:0; padding:0; width:100% !important; background-color:#f3f4f6; font-family:-apple-system,BlinkMacSystemFont,'Segoe UI',Roboto,Helvetica,Arial,sans-serif; color:#1f2937;">
    <table class="wrapper" width="100%" cellpadding="0" cellspacing="0" role="presentation" style="background-color:#f3f4f6; padding:32px 0;">
        <tr>
            <td align="center">
                <table class="content" width="100%" cellpadding="0" cellspacing="0" role="presentation" style="max-width:640px; width:100%;">
                    {{ $header ?? '' }}

                    <tr>
                        <td class="body" width="100%" cellpadding="0" cellspacing="0" style="padding:0 16px;">
                            <table class="inner-body" align="center" width="100%" cellpadding="0" cellspacing="0" role="presentation"
                                   style="background-color:#ffffff; border-radius:12px; width:100%; box-shadow:0 2px 8px rgba(0,0,0,.06); overflow:hidden;">
                                <!-- Body content -->
                                <tr>
                                    <td class="content-cell" style="padding:32px;">
                                        {{ Illuminate\Mail\Markdown::parse($slot) }}
                                        {{ $subcopy ?? '' }}
                                    </td>
                                </tr>
                            </table>
                        </td>
                    </tr>

                    {{ $footer ?? '' }}
                </table>
            </td>
        </tr>
    </table>
</body>
</html>
