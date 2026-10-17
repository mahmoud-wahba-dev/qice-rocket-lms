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
            line-height: 1.3; overflow: hidden; text-overflow: ellipsis;
        }
        .en  { direction: ltr;  text-align: left;   font-family: "DejaVu Sans", sans-serif; }
        .ar  { direction: rtl;  text-align: right;  font-family: "VazirCert", "DejaVu Sans", sans-serif; }
        .cen { direction: ltr;  text-align: center; font-family: "DejaVu Sans", sans-serif; }
        .car { direction: rtl;  text-align: center; font-family: "VazirCert", "DejaVu Sans", sans-serif; }
        .qr  { position: absolute; left: 40px; top: 800px; width: 72px; height: 72px; z-index: 2; }
        .qr img { width: 72px; height: 72px; display: block; }
    </style>
</head>
<body>
@php
    $d     = $data ?? [];
    $hours = str_pad((string)((int)($d['hours'] ?? 0)), 2, '0', STR_PAD_LEFT);
    $num   = $d['certificate_number'] ?? '';
    $start = $d['start_date']  ?? '';
    $end   = $d['end_date']    ?? '';
    $issue = $d['issue_date']  ?? '';
    $acc   = $d['accreditation_number'] ?? '';
    $officer  = $d['officer_name']  ?? 'مسؤول التدريب والاعتماد';
    $director = $d['director_name'] ?? 'مدير المركز';
    $nameEn   = $d['trainee_name_en']  ?? '';
    $nameAr   = $d['trainee_name_ar']  ?? '';
    $courseEn = $d['course_title_en']  ?? '';
    $courseAr = $d['course_title_ar']  ?? '';
@endphp
<div class="sheet">
    <img class="sheet-bg" src="{{ $backgroundUrl }}" width="1280" height="905" alt="">

    {{-- ── Certificate numbers ── --}}
    <div class="mask" style="left:140px;top:272px;width:210px;height:32px;"></div>
    <div class="field cen" style="left:140px;top:280px;width:210px;font-size:13px;">[ {{ $num }} ]</div>

    <div class="mask" style="left:960px;top:272px;width:210px;height:32px;"></div>
    <div class="field cen" style="left:960px;top:280px;width:210px;font-size:13px;">[ {{ $num }} ]</div>

    {{-- ── Trainee names ── --}}
    <div class="mask" style="left:65px;top:405px;width:480px;height:65px;"></div>
    <div class="field cen" style="left:65px;top:435px;width:480px;font-size:20px;">{{ $nameEn }}</div>

    <div class="mask" style="left:748px;top:405px;width:480px;height:65px;"></div>
    <div class="field car" style="left:748px;top:435px;width:480px;font-size:20px;">{{ $nameAr }}</div>

    {{-- ── Course titles ── --}}
    <div class="mask" style="left:65px;top:505px;width:480px;height:58px;"></div>
    <div class="field cen" style="left:65px;top:527px;width:480px;font-size:17px;">{{ $courseEn }}</div>

    <div class="mask" style="left:748px;top:505px;width:480px;height:58px;"></div>
    <div class="field car" style="left:748px;top:527px;width:480px;font-size:17px;">{{ $courseAr }}</div>

    {{-- ── Meta rows — EN left ── --}}
    <div class="mask" style="left:65px;top:582px;width:440px;height:28px;"></div>
    <div class="field en" style="left:68px;top:587px;width:440px;font-size:12px;">Duration: [ {{ $hours }} ] training hrs</div>

    <div class="mask" style="left:65px;top:612px;width:440px;height:28px;"></div>
    <div class="field en" style="left:68px;top:617px;width:440px;font-size:12px;">Course dates: [ {{ $start }} ] - [ {{ $end }} ]</div>

    <div class="mask" style="left:65px;top:642px;width:440px;height:28px;"></div>
    <div class="field en" style="left:68px;top:647px;width:440px;font-size:12px;">Issue date: [ {{ $issue }} ]</div>

    <div class="mask" style="left:65px;top:672px;width:440px;height:28px;"></div>
    <div class="field en" style="left:68px;top:677px;width:440px;font-size:12px;">Accreditation No.: [ {{ $acc }} ]</div>

    {{-- ── Meta rows — AR right ── --}}
    <div class="mask" style="left:778px;top:582px;width:440px;height:28px;"></div>
    <div class="field ar" style="left:778px;top:587px;width:440px;font-size:12px;">عدد الساعات: [ {{ $hours }} ] ساعة</div>

    <div class="mask" style="left:778px;top:612px;width:440px;height:28px;"></div>
    <div class="field ar" style="left:778px;top:617px;width:440px;font-size:12px;">فترة الدورة: [ {{ $start }} ] - [ {{ $end }} ]</div>

    <div class="mask" style="left:778px;top:642px;width:440px;height:28px;"></div>
    <div class="field ar" style="left:778px;top:647px;width:440px;font-size:12px;">تاريخ الإصدار: [ {{ $issue }} ]</div>

    <div class="mask" style="left:778px;top:672px;width:440px;height:28px;"></div>
    <div class="field ar" style="left:778px;top:677px;width:440px;font-size:12px;">رقم الاعتماد: [ {{ $acc }} ]</div>

    {{-- ── Signature area — Officer (left) ── --}}
    <div class="mask" style="left:60px;top:800px;width:280px;height:68px;"></div>
    <div class="field cen" style="left:60px;top:804px;width:280px;font-size:10px;">Training &amp; Accreditation Officer</div>
    <div class="field cen" style="left:60px;top:836px;width:280px;font-size:13px;">{{ $officer }}</div>

    {{-- ── Signature area — Director (right) ── --}}
    <div class="mask" style="left:960px;top:800px;width:280px;height:68px;"></div>
    <div class="field car" style="left:960px;top:804px;width:280px;font-size:12px;">مدير المركز</div>
    <div class="field cen" style="left:960px;top:824px;width:280px;font-size:13px;">{{ $director }}</div>

    {{-- ── QR code ── --}}
    @if (!empty($d['qr_html']))
        <div class="qr">{!! $d['qr_html'] !!}</div>
    @endif
</div>
</body>
</html>
