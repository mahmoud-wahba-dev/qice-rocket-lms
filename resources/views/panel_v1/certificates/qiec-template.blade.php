<!DOCTYPE html>
<html lang="ar">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <title>{{ $data['certificate_number'] ?? 'Certificate' }}</title>
    <style>
        @page { margin: 0; size: 960pt 679pt; }
        * { box-sizing: border-box; margin: 0; padding: 0; }
        html, body { width: 1280px; height: 905px; margin: 0; padding: 0; overflow: hidden; background: #fff; }
        .sheet { position: relative; width: 1280px; height: 905px; overflow: hidden; }
        .sheet-bg { position: absolute; left: 0; top: 0; width: 1280px; height: 905px; z-index: 0; }
        .mask { position: absolute; background: #FAF9F4; z-index: 1; }
        .field {
            position: absolute; z-index: 2; color: #0A3D38; font-weight: 700;
            line-height: 1.15; overflow: hidden; white-space: nowrap; text-overflow: ellipsis;
        }
        .en { direction: ltr; text-align: left; font-family: "DejaVu Sans", sans-serif; }
        .ar { direction: rtl; text-align: right; font-family: "VazirCert", "DejaVu Sans", sans-serif; }
        .center-en { direction: ltr; text-align: center; font-family: "DejaVu Sans", sans-serif; }
        .center-ar { direction: rtl; text-align: center; font-family: "VazirCert", "DejaVu Sans", sans-serif; }
        .qr { position: absolute; left: 40px; top: 800px; width: 72px; height: 72px; z-index: 2; }
        .qr img { width: 72px; height: 72px; display: block; }
    </style>
</head>
<body>
@php
    $d = $data ?? [];
    $hours = str_pad((string) ((int) ($d['hours'] ?? 0)), 2, '0', STR_PAD_LEFT);
    $num = $d['certificate_number'] ?? '';
    $start = $d['start_date'] ?? '';
    $end = $d['end_date'] ?? '';
    $issue = $d['issue_date'] ?? '';
    $acc = $d['accreditation_number'] ?? '';
@endphp
<div class="sheet">
    <img class="sheet-bg" src="{{ $backgroundUrl }}" width="1280" height="905" alt="">

    <div class="mask" style="left:155px;top:278px;width:175px;height:28px;"></div>
    <div class="mask" style="left:990px;top:278px;width:175px;height:28px;"></div>
    <div class="field center-en" style="left:155px;top:284px;width:175px;font-size:13px;">[ {{ $num }} ]</div>
    <div class="field center-en" style="left:990px;top:284px;width:175px;font-size:13px;">[ {{ $num }} ]</div>

    <div class="mask" style="left:72px;top:430px;width:463px;height:36px;"></div>
    <div class="mask" style="left:759px;top:430px;width:464px;height:36px;"></div>
    <div class="field center-en" style="left:72px;top:438px;width:463px;font-size:20px;">{{ $d['trainee_name_en'] ?? '' }}</div>
    <div class="field center-ar" style="left:759px;top:438px;width:464px;font-size:20px;">{{ $d['trainee_name_ar'] ?? '' }}</div>

    <div class="mask" style="left:72px;top:520px;width:463px;height:40px;"></div>
    <div class="mask" style="left:759px;top:520px;width:464px;height:40px;"></div>
    <div class="field center-en" style="left:72px;top:530px;width:463px;font-size:17px;">{{ $d['course_title_en'] ?? '' }}</div>
    <div class="field center-ar" style="left:759px;top:530px;width:464px;font-size:17px;">{{ $d['course_title_ar'] ?? '' }}</div>

    <div class="mask" style="left:70px;top:585px;width:280px;height:26px;"></div>
    <div class="mask" style="left:960px;top:585px;width:270px;height:26px;"></div>
    <div class="field en" style="left:70px;top:590px;width:280px;font-size:13px;">Duration: [ {{ $hours }} ] training hours</div>
    <div class="field ar" style="left:960px;top:590px;width:270px;font-size:13px;">عدد الساعات التدريبية: [ {{ $hours }} ] ساعة</div>

    <div class="mask" style="left:70px;top:615px;width:420px;height:26px;"></div>
    <div class="mask" style="left:850px;top:615px;width:380px;height:26px;"></div>
    <div class="field en" style="left:70px;top:620px;width:420px;font-size:12px;">Course dates: [ {{ $start }} ] - [ {{ $end }} ]</div>
    <div class="field ar" style="left:850px;top:620px;width:380px;font-size:12px;">فترة الدورة: [ {{ $start }} ] - [ {{ $end }} ]</div>

    <div class="mask" style="left:70px;top:645px;width:300px;height:26px;"></div>
    <div class="mask" style="left:960px;top:645px;width:270px;height:26px;"></div>
    <div class="field en" style="left:70px;top:650px;width:300px;font-size:12px;">Issue date: [ {{ $issue }} ]</div>
    <div class="field ar" style="left:960px;top:650px;width:270px;font-size:12px;">تاريخ الإصدار: [ {{ $issue }} ]</div>

    <div class="mask" style="left:70px;top:675px;width:360px;height:26px;"></div>
    <div class="mask" style="left:960px;top:675px;width:270px;height:26px;"></div>
    <div class="field en" style="left:70px;top:680px;width:360px;font-size:12px;">Accreditation Number: [ {{ $acc }} ]</div>
    <div class="field ar" style="left:960px;top:680px;width:270px;font-size:12px;">رقم الاعتماد: [ {{ $acc }} ]</div>

    <div class="mask" style="left:71px;top:820px;width:249px;height:22px;"></div>
    <div class="mask" style="left:960px;top:820px;width:248px;height:22px;"></div>
    <div class="field center-ar" style="left:71px;top:822px;width:249px;font-size:14px;">{{ $d['officer_name'] ?? '' }}</div>
    <div class="field center-ar" style="left:960px;top:822px;width:248px;font-size:14px;">{{ $d['director_name'] ?? '' }}</div>

    @if (!empty($d['qr_html']))
        <div class="qr">{!! $d['qr_html'] !!}</div>
    @endif
</div>
</body>
</html>
