<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>{{ $company_name }} — Estimate {{ $estimate->number }}</title>
    <style>
        @page { size: A4; margin: 18mm 14mm 18mm 14mm; }
        body { font-family: DejaVu Sans, sans-serif; font-size: 11px; color: #222; }
        .header { width: 100%; margin-bottom: 16px; border: none; }
        .header td { vertical-align: top; border: none; padding: 0; }
        .header-right { text-align: right; }
        .logo { max-width: 140px; max-height: 56px; }
        h1 { font-size: 18px; margin: 0 0 2px; color: #c45c1a; }
        .tagline { font-size: 10px; color: #555; margin-bottom: 6px; }
        .meta { width: 100%; border-collapse: collapse; margin: 12px 0; }
        .meta td { padding: 3px 6px 3px 0; vertical-align: top; }
        table.items { width: 100%; border-collapse: collapse; margin-top: 10px; }
        table.items th { background: #f4e6d8; border: 1px solid #d7c4ae; padding: 6px; font-size: 10px; text-align: left; }
        table.items td { border: 1px solid #e5d5c3; padding: 6px; vertical-align: top; }
        .num { text-align: right; white-space: nowrap; }
        .totals { width: 280px; margin-left: auto; margin-top: 12px; border-collapse: collapse; }
        .totals td { padding: 4px 6px; }
        .totals .grand { font-weight: bold; font-size: 13px; border-top: 2px solid #c45c1a; }
        .notes { margin-top: 14px; }
        .sign { margin-top: 28px; width: 100%; border: none; }
        .sign td { width: 50%; border: none; vertical-align: top; }
        .line { margin-top: 36px; border-top: 1px solid #999; width: 180px; padding-top: 4px; }
        .footer { margin-top: 24px; font-size: 9px; color: #666; }
    </style>
</head>
<body>
    <table class="header">
        <tr>
            <td>
                @if ($logo_data_uri)
                    <img src="{{ $logo_data_uri }}" alt="{{ $company_name }}" class="logo">
                @endif
                <h1>{{ $company_name }}</h1>
                @if ($tagline)
                    <div class="tagline">{{ $tagline }}</div>
                @endif
                @if ($address)<div>{{ $address }}</div>@endif
                @if ($phone)<div>{{ $phone }}</div>@endif
                @if ($email)<div>{{ $email }}</div>@endif
                @if ($website)<div>{{ $website }}</div>@endif
            </td>
            <td class="header-right">
                <strong>ESTIMATE</strong><br>
                {{ $estimate->number }}<br>
                Estimate Date: {{ $estimate->estimate_date?->format('d M Y') }}<br>
                Valid Until: {{ $estimate->valid_until?->format('d M Y') }}
            </td>
        </tr>
    </table>

    <table class="meta">
        <tr>
            <td><strong>Customer</strong><br>{{ $estimate->customer_name }}<br>{{ $estimate->customer_mobile }}</td>
            <td><strong>Event</strong><br>{{ $estimate->event_name ?: '—' }}<br>{{ $estimate->event_dates ?: '—' }}</td>
            <td><strong>Venue</strong><br>{{ $estimate->venue ?: '—' }}<br>{{ $estimate->city ?: '—' }}</td>
        </tr>
    </table>

    <table class="items">
        <thead>
            <tr>
                @foreach ($columns as $column)
                    <th>{{ $column }}</th>
                @endforeach
            </tr>
        </thead>
        <tbody>
            @foreach ($lines as $line)
                <tr>
                    <td>{{ $line['sn'] }}</td>
                    <td>
                        {{ $line['name'] }}
                        @if ($line['description'])
                            <br><span style="color:#555">{{ $line['description'] }}</span>
                        @endif
                    </td>
                    <td class="num">{{ $line['qty'] }}</td>
                    <td class="num">{{ $line['rate'] }}</td>
                    <td class="num">{{ $line['days'] }}</td>
                    <td class="num">{{ $line['amount'] }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <table class="totals">
        <tr><td>Subtotal</td><td class="num">{{ number_format((float) $estimate->subtotal, 2) }}</td></tr>
        @if ((float) $estimate->discount_amount > 0)
            <tr><td>Discount</td><td class="num">{{ number_format((float) $estimate->discount_amount, 2) }}</td></tr>
        @endif
        @if ((float) $estimate->additional_charges > 0)
            <tr><td>Additional charges</td><td class="num">{{ number_format((float) $estimate->additional_charges, 2) }}</td></tr>
        @endif
        @if ((float) $estimate->tax_amount > 0)
            <tr>
                <td>{{ $estimate->tax_label ?: 'Tax' }} ({{ number_format((float) $estimate->tax_rate, 2) }}%)</td>
                <td class="num">{{ number_format((float) $estimate->tax_amount, 2) }}</td>
            </tr>
        @endif
        <tr class="grand"><td>Grand Total</td><td class="num">INR {{ number_format((float) $estimate->grand_total, 2) }}</td></tr>
        <tr><td>Advance / Booking</td><td class="num">{{ number_format((float) $estimate->advance_amount, 2) }}</td></tr>
        <tr><td>Balance Due</td><td class="num">{{ number_format((float) $estimate->balance_due, 2) }}</td></tr>
    </table>

    <div class="notes">
        <strong>Quotation validity:</strong> Valid until {{ $estimate->valid_until?->format('d M Y') }}.
        @if ($estimate->payment_terms)
            <br><strong>Payment terms:</strong> {{ $estimate->payment_terms }}
        @endif
        @if ($estimate->payment_schedule)
            <br><strong>Payment schedule:</strong> {{ $estimate->payment_schedule }}
        @endif
        @if ($estimate->notes)
            <br><strong>Notes:</strong> {{ $estimate->notes }}
        @endif
        @if ($estimate->special_notes)
            <br><strong>Special notes / scope exclusions:</strong> {{ $estimate->special_notes }}
        @endif
    </div>

    <table class="sign">
        <tr>
            <td><div class="line">Customer acceptance</div></td>
            <td><div class="line">For {{ $company_name }}</div></td>
        </tr>
    </table>

    <div class="footer">This estimate is generated from Balaji Royal Events records. Amounts are in Indian Rupees unless stated otherwise.</div>
</body>
</html>
