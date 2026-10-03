<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta content="width=device-width, initial-scale=1, maximum-scale=1, shrink-to-fit=no" name="viewport">
    <title>{{ $pageTitle ?? 'فاتورة البيع' }}</title>
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <link rel="stylesheet" href="/assets/admin/vendor/bootstrap/bootstrap.min.css"/>
    <link rel="stylesheet" href="/assets/vendors/fontawesome/css/all.min.css"/>
    <link rel="stylesheet" href="/assets/admin/css/style.css">
    <link rel="stylesheet" href="/assets/admin/css/custom.css">
    <link rel="stylesheet" href="/assets/admin/css/components.css">
    <style>
        body { font-family: Cairo, Tahoma, sans-serif; background: #f4f6f9; }
        .invoice-sheet {
            max-width: 900px;
            margin: 24px auto;
            background: #fff;
            border-radius: 8px;
            border: 1px solid #e8e8e8;
            padding: 20px 24px;
        }
        .invoice-actions {
            display: flex;
            gap: 10px;
            justify-content: flex-start;
            flex-wrap: wrap;
            margin-top: 16px;
            padding-top: 12px;
            border-top: 1px solid #eee;
        }
        .invoice-head {
            display: flex;
            align-items: flex-start;
            justify-content: space-between;
            gap: 12px;
            margin-bottom: 12px;
        }
        .invoice-head h1 {
            margin: 0;
            font-size: 22px;
            font-weight: 700;
            color: #171347;
            line-height: 1.2;
        }
        .invoice-number {
            font-size: 14px;
            font-weight: 700;
            color: #34395e;
            white-space: nowrap;
        }
        .invoice-rule {
            border: 0;
            border-top: 1px solid #ececec;
            margin: 10px 0;
        }
        .invoice-meta {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 8px 24px;
            margin-bottom: 12px;
        }
        .invoice-meta-item {
            font-size: 13px;
            line-height: 1.45;
            color: #333;
        }
        .invoice-meta-item strong {
            display: block;
            margin-bottom: 2px;
            color: #191d21;
            font-size: 12px;
        }
        .invoice-section-title {
            margin: 0 0 8px;
            font-size: 15px;
            font-weight: 700;
            color: #171347;
        }
        .invoice-table {
            width: 100%;
            margin: 0 0 10px;
            border-collapse: collapse;
            font-size: 13px;
        }
        .invoice-table th,
        .invoice-table td {
            padding: 7px 8px;
            border-bottom: 1px solid #eee;
            vertical-align: middle;
            text-align: center;
        }
        .invoice-table th:first-child,
        .invoice-table td:first-child,
        .invoice-table th:nth-child(2),
        .invoice-table td:nth-child(2) {
            text-align: right;
        }
        .invoice-table th:last-child,
        .invoice-table td:last-child {
            text-align: left;
        }
        .invoice-table th {
            background: #f8f9fa;
            font-weight: 700;
            color: #34395e;
        }
        .invoice-totals {
            width: 280px;
            max-width: 100%;
            margin-right: auto;
            margin-left: 0;
        }
        .invoice-total-row {
            display: flex;
            justify-content: space-between;
            align-items: baseline;
            gap: 16px;
            padding: 4px 0;
            font-size: 13px;
        }
        .invoice-total-row .name { color: #98a6ad; }
        .invoice-total-row .value { font-weight: 700; color: #34395e; min-width: 90px; text-align: left; }
        .invoice-total-row.is-grand {
            margin-top: 4px;
            padding-top: 8px;
            border-top: 1px solid #ececec;
            font-size: 15px;
        }
        .invoice-total-row.is-grand .name { color: #191d21; font-weight: 700; }
        .invoice-total-row.is-grand .value { font-size: 18px; }

        @media (max-width: 640px) {
            .invoice-meta { grid-template-columns: 1fr; }
            .invoice-head { flex-direction: column; align-items: flex-start; }
            .invoice-totals { width: 100%; }
        }

        @page {
            size: A4;
            margin: 10mm;
        }

        @media print {
            html, body {
                background: #fff !important;
                margin: 0 !important;
                padding: 0 !important;
                height: auto !important;
                -webkit-print-color-adjust: exact;
                print-color-adjust: exact;
            }
            .no-print { display: none !important; }
            .invoice-sheet {
                max-width: none;
                margin: 0 !important;
                padding: 0 !important;
                border: none !important;
                border-radius: 0 !important;
                box-shadow: none !important;
            }
            .invoice-head h1 { font-size: 18px; }
            .invoice-number { font-size: 12px; }
            .invoice-meta { gap: 6px 16px; margin-bottom: 8px; }
            .invoice-meta-item { font-size: 11px; line-height: 1.35; }
            .invoice-meta-item strong { font-size: 11px; }
            .invoice-section-title { font-size: 13px; margin-bottom: 6px; }
            .invoice-table { font-size: 11px; margin-bottom: 8px; }
            .invoice-table th,
            .invoice-table td { padding: 5px 6px; }
            .invoice-totals { width: 240px; }
            .invoice-total-row { font-size: 11px; padding: 2px 0; }
            .invoice-total-row.is-grand { font-size: 13px; padding-top: 6px; }
            .invoice-total-row.is-grand .value { font-size: 15px; }
            .invoice-rule { margin: 6px 0; }
            /* Keep the whole invoice on one printed page */
            .invoice-sheet,
            .invoice-print,
            .invoice-meta,
            .invoice-table,
            .invoice-totals,
            tr {
                break-inside: avoid;
                page-break-inside: avoid;
            }
        }
    </style>
</head>
<body>
@php
    $sale = $sale ?? null;
    $itemTitle = $itemTitle ?? ($webinar->title ?? '—');
    $itemId = $itemId ?? ($webinar->id ?? ($sale->id ?? '—'));
    $itemType = $itemType ?? '—';
    $teacherName = $teacherName ?? ($webinar->teacher->full_name ?? '—');
    $creatorName = $creatorName ?? null;
    $partnerTeachers = $partnerTeachers ?? [];
    $siteName = $siteName ?? 'QIEC';
    $platformAddress = $platformAddress ?? '';
    $buyerName = $sale->buyer->full_name ?? '—';
    $orgName = ($creatorName && $creatorName !== $teacherName) ? $creatorName : '—';
    $teachersLine = collect([$teacherName])->merge($partnerTeachers)->filter()->implode('، ');
@endphp

<div class="invoice-sheet">
    <div class="invoice-print">
        <div class="invoice-head">
            <h1>{{ $siteName }}</h1>
            <div class="invoice-number">رقم الفاتورة: #{{ $sale->id ?? '—' }}</div>
        </div>

        <hr class="invoice-rule">

        <div class="invoice-meta">
            <div class="invoice-meta-item">
                <strong>المتدرب</strong>
                {{ $buyerName }}
                @if (!empty($sale->buyer->email))
                    — {{ $sale->buyer->email }}
                @endif
                @if (!empty($sale->buyer->mobile))
                    — {{ $sale->buyer->mobile }}
                @endif
            </div>
            <div class="invoice-meta-item">
                <strong>عنوان المنصة</strong>
                {{ strip_tags($platformAddress ?: '—') }}
            </div>
            <div class="invoice-meta-item">
                <strong>المنظمة / المنشئ</strong>
                {{ $orgName }}
            </div>
            <div class="invoice-meta-item">
                <strong>تاريخ الشراء</strong>
                {{ !empty($sale->created_at) ? dateTimeFormat($sale->created_at, 'Y M j | H:i') : '—' }}
                @if (!empty($sale->refund_at))
                    <span class="text-danger"> — مستردة: {{ dateTimeFormat($sale->refund_at, 'Y M j | H:i') }}</span>
                @endif
            </div>
            <div class="invoice-meta-item">
                <strong>المدرب</strong>
                {{ $teachersLine ?: '—' }}
            </div>
        </div>

        <div class="invoice-section-title">ملخص الطلب</div>
        <table class="invoice-table">
            <thead>
                <tr>
                    <th style="width:56px">#</th>
                    <th>العنصر</th>
                    <th>النوع</th>
                    <th>السعر</th>
                    <th>الخصم</th>
                    <th>الإجمالي</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td>{{ $itemId }}</td>
                    <td>{{ $itemTitle }}</td>
                    <td>{{ $itemType }}</td>
                    <td>
                        @if (!empty($sale->amount))
                            {{ handlePrice($sale->amount) }}
                        @else
                            مجاني
                        @endif
                    </td>
                    <td>
                        @if (!empty($sale->discount))
                            {{ handlePrice($sale->discount) }}
                        @else
                            —
                        @endif
                    </td>
                    <td>
                        @if (!empty($sale->total_amount))
                            {{ handlePrice($sale->total_amount) }}
                        @else
                            {{ handlePrice(0) }}
                        @endif
                    </td>
                </tr>
            </tbody>
        </table>

        <div class="invoice-totals">
            <div class="invoice-total-row">
                <span class="name">المجموع الفرعي</span>
                <span class="value">{{ handlePrice($sale->amount ?? 0) }}</span>
            </div>
            <div class="invoice-total-row">
                <span class="name">الضريبة ({{ getFinancialSettings('tax') }}%)</span>
                <span class="value">
                    @if (!empty($sale->tax))
                        {{ handlePrice($sale->tax) }}
                    @else
                        —
                    @endif
                </span>
            </div>
            <div class="invoice-total-row">
                <span class="name">الخصم</span>
                <span class="value">
                    @if (!empty($sale->discount))
                        {{ handlePrice($sale->discount) }}
                    @else
                        —
                    @endif
                </span>
            </div>
            <div class="invoice-total-row is-grand">
                <span class="name">الإجمالي</span>
                <span class="value">
                    @if (!empty($sale->total_amount))
                        {{ handlePrice($sale->total_amount) }}
                    @else
                        —
                    @endif
                </span>
            </div>
        </div>
    </div>

    <div class="invoice-actions no-print">
        <button type="button" onclick="window.print()" class="btn btn-warning btn-icon icon-left">
            <i class="fas fa-print"></i> طباعة
        </button>
        <a href="{{ route('panel.v1.admin.sales.home') }}" class="btn btn-primary">
            العودة للمبيعات
        </a>
    </div>
</div>
</body>
</html>
