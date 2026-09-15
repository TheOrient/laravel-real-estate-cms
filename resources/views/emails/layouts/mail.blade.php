<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $title ?? config('app.name') }}</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, 'Helvetica Neue', Arial, sans-serif;
            line-height: 1.6;
            color: #333;
            background-color: #f3f4f6;
            padding: 20px 10px;
        }

        .email-wrapper {
            max-width: 600px;
            margin: 0 auto;
            background: #ffffff;
            border-radius: 8px;
            overflow: hidden;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
        }

        /* Header Styles */
        .email-header {
            padding: 30px 20px;
            text-align: center;
            color: white;
            background: {{ $headerColor ?? '#059669' }};
        }

        .email-header.success { background: #059669; }
        .email-header.error { background: #dc2626; }
        .email-header.warning { background: #f59e0b; }
        .email-header.info { background: #2563eb; }
        .email-header.primary { background: #3b82f6; }
        .email-header.green { background: #10b981; }
        .email-header.red { background: #ef4444; }

        .email-icon {
            font-size: 48px;
            margin-bottom: 15px;
            line-height: 1;
        }

        .email-header h1 {
            margin: 0;
            font-size: 24px;
            font-weight: 600;
        }

        .email-header p {
            margin: 10px 0 0;
            font-size: 14px;
            opacity: 0.9;
        }

        /* Content Styles */
        .email-content {
            padding: 30px 25px;
            background: #ffffff;
        }

        .email-content > p {
            margin: 0 0 15px;
            color: #374151;
        }

        .email-content h2 {
            font-size: 20px;
            margin: 25px 0 15px;
            color: #1f2937;
        }

        .email-content h3 {
            font-size: 18px;
            margin: 20px 0 10px;
            color: #374151;
        }

        /* Info Box Styles */
        .info-box {
            background: #ffffff;
            border: 1px solid #e5e7eb;
            border-radius: 6px;
            padding: 20px;
            margin: 20px 0;
        }

        .info-box h3 {
            margin-top: 0;
        }

        .info-box.success {
            background: #dcfce7;
            border-color: #059669;
            border-left: 4px solid #059669;
        }

        .info-box.error {
            background: #fee2e2;
            border-color: #dc2626;
            border-left: 4px solid #dc2626;
        }

        .info-box.warning {
            background: #fef3c7;
            border-color: #f59e0b;
            border-left: 4px solid #f59e0b;
        }

        .info-box.info {
            background: #eff6ff;
            border-color: #3b82f6;
            border-left: 4px solid #3b82f6;
        }

        /* Field Styles */
        .field-group {
            margin: 15px 0;
        }

        .field {
            padding: 10px 0;
            border-bottom: 1px solid #e5e7eb;
        }

        .field:last-child {
            border-bottom: none;
        }

        .field strong {
            color: #1f2937;
            display: inline-block;
            min-width: 140px;
        }

        /* Button Styles */
        .button-group {
            text-align: center;
            margin: 25px 0;
        }

        .button {
            display: inline-block;
            padding: 12px 30px;
            margin: 8px 5px;
            background: #059669;
            color: #ffffff !important;
            text-decoration: none;
            border-radius: 6px;
            font-weight: 500;
            transition: background 0.3s;
        }

        .button:hover {
            opacity: 0.9;
        }

        .button.primary { background: #3b82f6; }
        .button.secondary { background: #6b7280; }
        .button.success { background: #059669; }
        .button.error { background: #dc2626; }
        .button.warning { background: #f59e0b; }
        .button.info { background: #2563eb; }

        /* List Styles */
        .email-content ul {
            margin: 15px 0;
            padding-left: 25px;
        }

        .email-content li {
            margin: 8px 0;
            color: #374151;
        }

        /* Divider */
        .divider {
            height: 1px;
            background: #e5e7eb;
            margin: 25px 0;
        }

        /* Message Box */
        .message-box {
            background: #f9fafb;
            border: 1px solid #e5e7eb;
            border-radius: 6px;
            padding: 15px;
            margin: 20px 0;
        }

        .message-box p {
            margin: 5px 0;
        }

        /* Footer Styles */
        .email-footer {
            padding: 25px;
            background: #f9fafb;
            border-top: 1px solid #e5e7eb;
            text-align: center;
        }

        .email-footer p {
            margin: 8px 0;
            font-size: 13px;
            color: #6b7280;
        }

        .email-footer strong {
            color: #374151;
        }

        /* Responsive */
        @media only screen and (max-width: 600px) {
            body {
                padding: 10px 5px;
            }

            .email-content {
                padding: 20px 15px;
            }

            .email-header h1 {
                font-size: 20px;
            }

            .button {
                display: block;
                margin: 10px 0;
            }

            .field strong {
                display: block;
                margin-bottom: 5px;
            }
        }
    </style>
</head>
<body>
    <div class="email-wrapper">
        {{-- Header Section --}}
        <div class="email-header {{ $headerType ?? 'success' }}">
            @if(isset($icon))
                <div class="email-icon">{{ $icon }}</div>
            @endif

            <h1>{{ $heading }}</h1>

            @if(isset($subheading))
                <p>{{ $subheading }}</p>
            @endif
        </div>

        {{-- Content Section --}}
        <div class="email-content">
            {{ $slot }}
        </div>

        {{-- Footer Section --}}
        <div class="email-footer">
            @if(isset($footer))
                {{ $footer }}
            @else
                <p>{{ __('mail.auto_generated') }}</p>
                @if(isset($additionalFooter))
                    {!! $additionalFooter !!}
                @endif
                <p>&copy; {{ date('Y') }} {{ config('app.name') }}. {{ __('mail.all_rights_reserved') }}</p>
            @endif
        </div>
    </div>
</body>
</html>
