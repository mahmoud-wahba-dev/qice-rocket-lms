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
        .qr-box { position: absolute; left: 592px; top: 603px; width: 96px; height: 96px; z-index: 2; background: #FAF9F4; }
        .qr-box img { width: 96px; height: 96px; display: block; }
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
    $nameEn   = $d['trainee_name_en']  ?? '';
    $nameAr   = $d['trainee_name_ar']  ?? '';
    $courseEn = $d['course_title_en']  ?? '';
    $courseAr = $d['course_title_ar']  ?? '';
    $accByEn  = $d['accredited_by_en'] ?? 'Quality & Excellence Center';
    $accByAr  = $d['accredited_by_ar'] ?? 'مركز الجودة والتميز';
    if (mb_strlen($accByEn) > 18) { $accByEn = mb_substr($accByEn, 0, 18); }
    if (mb_strlen($accByAr) > 14) { $accByAr = mb_substr($accByAr, 0, 14); }
    $qrHtml   = $d['qr_html'] ?? '';
@endphp
<div class="sheet">
    <img class="sheet-bg" src="{{ $backgroundUrl }}" width="1280" height="905" alt="">

    <div class="mask" style="left:186px;top:284px;width:138px;height:22px;"></div>
    <div class="field en" style="left:188px;top:288px;width:136px;font-size:11px;">[ {{ $num }} ]</div>

    <div class="mask" style="left:990px;top:284px;width:120px;height:22px;"></div>
    <div class="field en" style="left:990px;top:288px;width:118px;text-align:right;font-size:11px;">[ {{ $num }} ]</div>

    <div class="mask" style="left:70px;top:428px;width:350px;height:32px;"></div>
    <div class="field en" style="left:74px;top:434px;width:346px;font-size:18px;">{{ $nameEn }}</div>

    <div class="mask" style="left:848px;top:424px;width:380px;height:34px;"></div>
    <div class="field ar" style="left:848px;top:432px;width:357px;font-size:18px;">{{ $nameAr }}</div>

    <div class="mask" style="left:70px;top:524px;width:200px;height:30px;"></div>
    <div class="field en" style="left:74px;top:530px;width:196px;font-size:14px;">{{ $courseEn }}</div>

    <div class="mask" style="left:960px;top:524px;width:268px;height:32px;"></div>
    <div class="field ar" style="left:960px;top:530px;width:245px;font-size:14px;">{{ $courseAr }}</div>

    <div class="mask" style="left:148px;top:590px;width:40px;height:20px;"></div>
    <div class="field en" style="left:150px;top:594px;width:40px;font-size:11px;font-weight:normal;">[ {{ $hours }} ]</div>

    <div class="mask" style="left:178px;top:618px;width:304px;height:24px;"></div>
    <div class="field en" style="left:182px;top:624px;width:300px;font-size:11px;font-weight:normal;">[ {{ $start }} ] – [ {{ $end }} ]</div>

    <div class="mask" style="left:156px;top:650px;width:148px;height:24px;"></div>
    <div class="field en" style="left:160px;top:656px;width:144px;font-size:11px;font-weight:normal;">[ {{ $issue }} ]</div>

    <div class="mask" style="left:250px;top:682px;width:148px;height:24px;"></div>
    <div class="field en" style="left:254px;top:688px;width:144px;font-size:11px;font-weight:normal;">[ {{ $accByEn }} ]</div>

    <div class="mask" style="left:250px;top:714px;width:148px;height:24px;"></div>
    <div class="field en" style="left:254px;top:720px;width:144px;font-size:11px;font-weight:normal;">[ {{ $acc }} ]</div>

    <div class="mask" style="left:1000px;top:588px;width:50px;height:22px;"></div>
    <div class="field en" style="left:1002px;top:594px;width:48px;font-size:11px;font-weight:normal;">[ {{ $hours }} ]</div>

    <div class="mask" style="left:865px;top:618px;width:268px;height:24px;"></div>
    <div class="field en" style="left:865px;top:624px;width:263px;text-align:right;font-size:11px;font-weight:normal;">[ {{ $start }} ] - [ {{ $end }} ]</div>

    <div class="mask" style="left:995px;top:650px;width:120px;height:24px;"></div>
    <div class="field en" style="left:995px;top:656px;width:117px;text-align:right;font-size:11px;font-weight:normal;">[ {{ $issue }} ]</div>

    <div class="mask" style="left:1012px;top:682px;width:100px;height:24px;"></div>
    <div class="field ar" style="left:1012px;top:688px;width:98px;font-size:11px;font-weight:normal;">[ {{ $accByAr }} ]</div>

    <div class="mask" style="left:1000px;top:714px;width:120px;height:24px;"></div>
    <div class="field en" style="left:1000px;top:720px;width:116px;text-align:right;font-size:11px;font-weight:normal;">[ {{ $acc }} ]</div>

    @if (!empty($qrHtml))
        <div class="qr-box">{!! $qrHtml !!}</div>
    @endif
</div>
</body>
</html>
