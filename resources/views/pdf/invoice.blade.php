<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>{{ $invoiceType }} #{{ $invoiceNumber }}</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }

        body {
            font-family: DejaVu Sans, Arial, sans-serif;
            font-size: 12px;
            color: #1e293b;
            background: #fff;
            padding: 40px 48px;
        }

        /* ── Header ──────────────────────────────────────────── */
        .header {
            display: table;
            width: 100%;
            margin-bottom: 32px;
        }
        .header-left, .header-right {
            display: table-cell;
            vertical-align: middle;
            width: 50%;
        }
        .header-right { text-align: right; }

        .logo {
            max-height: 52px;
            max-width: 200px;
        }

        .invoice-title {
            font-size: 28px;
            font-weight: 700;
            color: #3730a3;
            letter-spacing: -0.5px;
        }
        .invoice-subtitle {
            font-size: 11px;
            color: #64748b;
            margin-top: 2px;
        }

        /* ── Divider ─────────────────────────────────────────── */
        .divider {
            border: none;
            border-top: 2px solid #e2e8f0;
            margin: 20px 0;
        }
        .divider-accent {
            border-top-color: #4f46e5;
        }

        /* ── Meta Grid ───────────────────────────────────────── */
        .meta-table {
            width: 100%;
            margin-bottom: 28px;
        }
        .meta-table td {
            vertical-align: top;
            width: 50%;
            padding: 0;
        }
        .meta-table td:last-child {
            text-align: right;
        }

        .address-block .label {
            font-size: 9px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.1em;
            color: #94a3b8;
            margin-bottom: 4px;
        }
        .address-block .value {
            font-size: 12px;
            color: #1e293b;
            line-height: 1.6;
            white-space: pre-line;
        }

        .meta-info-block {
            display: inline-block;
            text-align: right;
        }
        .meta-row {
            display: table;
            width: 100%;
            margin-bottom: 6px;
        }
        .meta-row .ml { display: table-cell; text-align: left; }
        .meta-row .mr { display: table-cell; text-align: right; padding-left: 24px; }
        .meta-key {
            font-size: 10px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.08em;
            color: #94a3b8;
        }
        .meta-val {
            font-size: 12px;
            font-weight: 600;
            color: #1e293b;
        }

        /* ── Items Table ─────────────────────────────────────── */
        .items-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 24px;
        }
        .items-table thead tr {
            background: #4f46e5;
            color: #fff;
        }
        .items-table thead th {
            padding: 10px 12px;
            font-size: 10px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.08em;
            text-align: left;
        }
        .items-table thead th:last-child,
        .items-table thead th.right { text-align: right; }

        .items-table tbody tr { border-bottom: 1px solid #f1f5f9; }
        .items-table tbody tr:nth-child(even) { background: #f8fafc; }
        .items-table tbody td {
            padding: 10px 12px;
            font-size: 12px;
            color: #334155;
            vertical-align: middle;
        }
        .items-table tbody td.right { text-align: right; font-weight: 600; }
        .items-table tbody td.muted  { color: #64748b; }

        .desc-main { font-weight: 500; color: #1e293b; }

        /* ── Totals ──────────────────────────────────────────── */
        .totals-wrap {
            display: table;
            width: 100%;
            margin-bottom: 28px;
        }
        .totals-notes { display: table-cell; width: 50%; vertical-align: top; padding-right: 24px; }
        .totals-block { display: table-cell; width: 50%; vertical-align: top; }

        .notes-label {
            font-size: 9px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.1em;
            color: #94a3b8;
            margin-bottom: 6px;
        }
        .notes-text {
            font-size: 11px;
            color: #475569;
            line-height: 1.6;
            white-space: pre-line;
        }

        .total-row {
            display: table;
            width: 100%;
            margin-bottom: 6px;
        }
        .total-row td-l { display: table-cell; color: #64748b; font-size: 12px; }
        .total-row td-r { display: table-cell; font-weight: 600; text-align: right; font-size: 12px; }

        .totals-inner { width: 100%; border-collapse: collapse; }
        .totals-inner td { padding: 5px 0; font-size: 12px; color: #475569; }
        .totals-inner td:last-child { text-align: right; font-weight: 600; color: #1e293b; }

        .totals-inner .balance-row td {
            padding-top: 12px;
            border-top: 2px solid #4f46e5;
            font-size: 15px;
            font-weight: 700;
            color: #4f46e5;
        }

        /* ── Footer ──────────────────────────────────────────── */
        .footer {
            margin-top: 40px;
            text-align: center;
            font-size: 10px;
            color: #cbd5e1;
            border-top: 1px solid #f1f5f9;
            padding-top: 12px;
        }
    </style>
</head>
<body>
@php
    $fmt = function(float $n) use ($currencySymbol, $currencyDecimals): string {
        return $currencySymbol . ' ' . number_format($n, $currencyDecimals, '.', ',');
    };
@endphp

    <!-- ══ Header ══════════════════════════════════════════════ -->
    <div class="header">
        <div class="header-left">
            <img src="{{ public_path('javaradigital-logo.png') }}" class="logo" alt="Logo" />
        </div>
        <div class="header-right">
            <div class="invoice-title">{{ strtoupper($invoiceType) }}</div>
            <div class="invoice-subtitle"># {{ $invoiceNumber }}</div>
        </div>
    </div>

    <hr class="divider divider-accent" />

    <!-- ══ From / To / Meta ════════════════════════════════════ -->
    <table class="meta-table">
        <tr>
            <td>
                @if($fromText)
                <div class="address-block">
                    <div class="label">From</div>
                    <div class="value">{{ $fromText }}</div>
                </div>
                @endif
            </td>
            <td style="text-align:right;">
                <table class="totals-inner" style="width:auto; margin-left:auto;">
                    <tr>
                        <td style="color:#94a3b8; font-size:10px; font-weight:700; text-transform:uppercase; letter-spacing:0.08em; text-align:left; padding-right:20px;">Date</td>
                        <td style="text-align:right; font-weight:600;">{{ $date }}</td>
                    </tr>
                    <tr>
                        <td style="color:#94a3b8; font-size:10px; font-weight:700; text-transform:uppercase; letter-spacing:0.08em; text-align:left; padding-right:20px;">Terms</td>
                        <td style="text-align:right; font-weight:600;">{{ $terms }}</td>
                    </tr>
                    @if($poNumber)
                    <tr>
                        <td style="color:#94a3b8; font-size:10px; font-weight:700; text-transform:uppercase; letter-spacing:0.08em; text-align:left; padding-right:20px;">PO #</td>
                        <td style="text-align:right; font-weight:600;">{{ $poNumber }}</td>
                    </tr>
                    @endif
                </table>
            </td>
        </tr>
    </table>

    @if($toText)
    <div class="address-block" style="margin-bottom:24px;">
        <div class="label">Bill To</div>
        <div class="value">{{ $toText }}</div>
    </div>
    @endif

    <hr class="divider" />

    <!-- ══ Items ════════════════════════════════════════════════ -->
    <table class="items-table">
        <thead>
            <tr>
                <th style="width:50%;">Description</th>
                <th style="width:20%;">Unit Price</th>
                <th style="width:10%;" class="right">Qty</th>
                <th style="width:20%;" class="right">Amount</th>
            </tr>
        </thead>
        <tbody>
            @foreach($items as $item)
            @php
                $amount = (float)($item['price'] ?? 0) * (int)($item['qty'] ?? 1);
            @endphp
            <tr>
                <td class="desc-main">{{ $item['description'] ?: '—' }}</td>
                <td class="muted">{{ $fmt((float)($item['price'] ?? 0)) }}</td>
                <td class="right muted">{{ $item['qty'] ?? 1 }}</td>
                <td class="right">{{ $fmt($amount) }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>

    <!-- ══ Totals + Notes ══════════════════════════════════════ -->
    <div class="totals-wrap">
        <!-- Notes -->
        <div class="totals-notes">
            @if($extraNotes)
            <div class="notes-label">Notes</div>
            <div class="notes-text">{{ $extraNotes }}</div>
            @endif

            @if($paymentDetails)
            <div class="notes-label" style="margin-top:12px;">Payment Details</div>
            <div class="notes-text">{{ $paymentDetails }}</div>
            @endif
        </div>

        <!-- Totals -->
        <div class="totals-block">
            <table class="totals-inner">
                <tr>
                    <td>Subtotal</td>
                    <td>{{ $fmt($subtotal) }}</td>
                </tr>
                @if($taxAmount > 0)
                <tr>
                    <td>Tax ({{ $taxRate }}%)</td>
                    <td>{{ $fmt($taxAmount) }}</td>
                </tr>
                @endif
                @if($discountAmount > 0)
                <tr>
                    <td>Discount</td>
                    <td style="color:#ef4444;">–{{ $fmt($discountAmount) }}</td>
                </tr>
                @endif
                @if($shippingAmount > 0)
                <tr>
                    <td>Shipping</td>
                    <td>{{ $fmt($shippingAmount) }}</td>
                </tr>
                @endif
                <tr class="balance-row">
                    <td>Balance Due</td>
                    <td>{{ $fmt($balanceDue) }}</td>
                </tr>
            </table>
        </div>
    </div>

    <!-- ══ Footer ══════════════════════════════════════════════ -->
    <div class="footer">
        Generated by JavaraDigital Invoice Maker &nbsp;·&nbsp; tools.javara.digital
    </div>

</body>
</html>
