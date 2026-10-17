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
    $accByEn  = $d['accredited_by_en'] ?? 'Quality & Excellence Center';
    $accByAr  = $d['accredited_by_ar'] ?? 'مركز الجودة والتميز';
@endphp
<div class="sheet">
    <img class="sheet-bg" src="{{ $backgroundUrl }}" width="1280" height="905" alt="">

    {{-- ── Certificate numbers ── --}}
    <div class="mask" style="left:186px;top:280px;width:140px;height:24px;"></div>
    <div class="field en" style="left:188px;top:284px;width:140px;font-size:12px;">[ {{ $num }} ]</div>

    <div class="mask" style="left:988px;top:278px;width:124px;height:24px;"></div>
    <div class="field en" style="left:988px;top:284px;width:120px;text-align:right;font-size:12px;">[ {{ $num }} ]</div>

    {{-- ── Trainee names ── --}}
    <div class="mask" style="left:70px;top:424px;width:480px;height:38px;"></div>
    <div class="field en" style="left:74px;top:430px;width:480px;font-size:20px;text-transform:uppercase;">{{ $nameEn }}</div>

    <div class="mask" style="left:730px;top:420px;width:500px;height:40px;"></div>
    <div class="field ar" style="left:730px;top:426px;width:490px;font-size:20px;">{{ $nameAr }}</div>

    {{-- ── Course titles ── --}}
    <div class="mask" style="left:70px;top:520px;width:480px;height:34px;"></div>
    <div class="field en" style="left:74px;top:524px;width:480px;font-size:16px;">{{ $courseEn }}</div>

    <div class="mask" style="left:730px;top:518px;width:500px;height:36px;"></div>
    <div class="field ar" style="left:730px;top:522px;width:490px;font-size:16px;">{{ $courseAr }}</div>

    {{-- ── Meta rows — EN left ── --}}
    <div class="mask" style="left:147px;top:590px;width:53px;height:18px;"></div>
    <div class="field en" style="left:149px;top:592px;width:60px;font-size:11px;font-weight:normal;">[ {{ $hours }} ]</div>

    <div class="mask" style="left:180px;top:620px;width:270px;height:18px;"></div>
    <div class="field en" style="left:182px;top:622px;width:270px;font-size:11px;font-weight:normal;">[ {{ $start }} ] – [ {{ $end }} ]</div>

    <div class="mask" style="left:156px;top:652px;width:130px;height:18px;"></div>
    <div class="field en" style="left:158px;top:654px;width:130px;font-size:11px;font-weight:normal;">[ {{ $issue }} ]</div>

    <div class="mask" style="left:250px;top:684px;width:130px;height:18px;"></div>
    <div class="field en" style="left:252px;top:686px;width:130px;font-size:11px;font-weight:normal;">[ {{ $accByEn }} ]</div>

    <div class="mask" style="left:250px;top:716px;width:130px;height:18px;"></div>
    <div class="field en" style="left:252px;top:718px;width:130px;font-size:11px;font-weight:normal;">[ {{ $acc }} ]</div>

    {{-- ── Meta rows — AR right ── --}}
    <div class="mask" style="left:1006px;top:588px;width:52px;height:20px;"></div>
    <div class="field en" style="left:1006px;top:590px;width:50px;text-align:right;font-size:11px;font-weight:normal;">[ {{ $hours }} ]</div>

    <div class="mask" style="left:900px;top:620px;width:237px;height:18px;"></div>
    <div class="field en" style="left:900px;top:622px;width:230px;text-align:right;font-size:11px;font-weight:normal;">[ {{ $start }} ] - [ {{ $end }} ]</div>

    <div class="mask" style="left:1008px;top:652px;width:118px;height:18px;"></div>
    <div class="field en" style="left:1008px;top:654px;width:114px;text-align:right;font-size:11px;font-weight:normal;">[ {{ $issue }} ]</div>

    <div class="mask" style="left:1012px;top:684px;width:75px;height:18px;"></div>
    <div class="field en" style="left:1012px;top:686px;width:72px;text-align:right;font-size:11px;font-weight:normal;">[ {{ $accByAr }} ]</div>

    <div class="mask" style="left:1012px;top:716px;width:112px;height:18px;"></div>
    <div class="field en" style="left:1012px;top:718px;width:108px;text-align:right;font-size:11px;font-weight:normal;">[ {{ $acc }} ]</div>

    {{-- ── Signature area (placeholders only) ── --}}
    <div class="mask" style="left:80px;top:846px;width:240px;height:20px;"></div>
    <div class="field cen" style="left:80px;top:846px;width:240px;font-size:13px;">{{ $officer }}</div>

    <div class="mask" style="left:980px;top:846px;width:240px;height:20px;"></div>
    <div class="field car" style="left:980px;top:846px;width:240px;font-size:13px;">{{ $director }}</div>
</div>

</body>
</html>
