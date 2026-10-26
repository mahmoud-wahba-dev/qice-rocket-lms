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
        .qr-box { position: absolute; left: 594px; top: 606px; width: 90px; height: 90px; z-index: 2; background: #FAF9F4; }
        .qr-box img { width: 90px; height: 90px; display: block; }
    </style>
</head>
<body>
@php
    $d     = $data ?? [];
    $hours = str_pad((string)((int)($d['hours'] ?? 0)), 2, '0', STR_PAD_LEFT);
    $num   = $d['certificate_number'] ?? '';
    $spaceDate = static function (string $value): string {
        $value = trim($value);
        if ($value === '') {
            return '';
        }
        if (preg_match('/^(\d{1,4})\s*[\/.\-]\s*(\d{1,4})\s*[\/.\-]\s*(\d{1,4})$/', $value, $m)) {
            return $m[1] . '  /  ' . $m[2] . '  /  ' . $m[3];
        }
        return $value;
    };
    $start = $spaceDate((string)($d['start_date'] ?? ''));
    $end   = $spaceDate((string)($d['end_date'] ?? ''));
    $issue = $spaceDate((string)($d['issue_date'] ?? ''));
    $acc   = $d['accreditation_number'] ?? '';
    $nameEn   = $d['trainee_name_en']  ?? '';
    $nameAr   = $d['trainee_name_ar']  ?? '';
    $courseEn = $d['course_title_en']  ?? '';
    $courseAr = $d['course_title_ar']  ?? '';
    $accByEn  = $d['accredited_by_en'] ?? 'Quality & Excellence Center';
    $accByAr  = $d['accredited_by_ar'] ?? 'مركز الجودة والتميز';
    $accByAr  = preg_replace('/^\s*الدورة\s+معتمدة\s+من\s*:?\s*/u', '', (string) $accByAr) ?: $accByAr;
    $qrHtml   = $d['qr_html'] ?? '';
    $accByArSize = mb_strlen($accByAr) > 30 ? '11px' : (mb_strlen($accByAr) > 22 ? '12px' : '14px');
    $accByEnSize = mb_strlen($accByEn) > 40 ? '10px' : (mb_strlen($accByEn) > 28 ? '11px' : '12px');
@endphp
<div class="sheet">
    <img class="sheet-bg" src="{{ $backgroundUrl }}" width="1280" height="905" alt="">

    <div class="mask" style="left:186px;top:284px;width:138px;height:22px;"></div>
    <div class="field en" style="left:188px;top:288px;width:136px;font-size:11px;">[ {{ $num }} ]</div>

    <div class="mask" style="left:990px;top:284px;width:120px;height:22px;"></div>
    <div class="field en" style="left:992px;top:288px;width:116px;font-size:11px;">[ {{ $num }} ]</div>

    <div class="mask" style="left:70px;top:428px;width:350px;height:32px;"></div>
    <div class="field en" style="left:74px;top:434px;width:346px;font-size:18px;">{{ $nameEn }}</div>

    @php
        $nameArLen = mb_strlen(trim((string) $nameAr));
        $nameArSize = $nameArLen > 30 ? max(16, (int) round(22 * 30 / max(1, $nameArLen))) : 22;
    @endphp
    <div class="mask" style="left:780px;top:418px;width:450px;height:42px;"></div>
    <div class="field ar" style="left:780px;top:428px;width:425px;font-size:{{ $nameArSize }}px;">{{ $nameAr }}</div>

    @php
        $courseEnDraw = preg_match('/[\x{0600}-\x{06FF}]/u', (string) $courseEn) ? '' : (string) $courseEn;
        $courseEnLen = mb_strlen(trim($courseEnDraw));
        $courseArLen = mb_strlen(trim((string) $courseAr));
        $courseEnSize = $courseEnLen > 40 ? max(12, (int) round(16 * 40 / max(1, $courseEnLen))) : 16;
        $courseArSize = $courseArLen > 36 ? max(12, (int) round(16 * 36 / max(1, $courseArLen))) : 16;
    @endphp
    <div class="mask" style="left:70px;top:516px;width:460px;height:42px;"></div>
    @if ($courseEnDraw !== '')
    <div class="field en" style="left:74px;top:528px;width:450px;font-size:{{ $courseEnSize }}px;white-space:nowrap;">{{ $courseEnDraw }}</div>
    @endif

    <div class="mask" style="left:780px;top:516px;width:440px;height:42px;"></div>
    <div class="field ar" style="left:780px;top:528px;width:425px;font-size:{{ $courseArSize }}px;white-space:nowrap;">{{ $courseAr }}</div>

    <div class="mask" style="left:148px;top:590px;width:40px;height:20px;"></div>
    <div class="field en" style="left:150px;top:594px;width:40px;font-size:11px;font-weight:normal;">[ {{ $hours }} ]</div>

    <div class="mask" style="left:178px;top:616px;width:360px;height:28px;"></div>
    <div class="field en" style="left:182px;top:624px;width:356px;font-size:11px;font-weight:normal;">[ {{ $start }} ]  –  [ {{ $end }} ]</div>

    <div class="mask" style="left:158px;top:648px;width:200px;height:28px;"></div>
    <div class="field en" style="left:162px;top:656px;width:196px;font-size:11px;font-weight:normal;">[ {{ $issue }} ]</div>

    <div class="mask" style="left:70px;top:672px;width:500px;height:36px;background:#FAF9F4;"></div>
    <div class="field en" style="left:74px;top:686px;width:500px;font-size:12px;font-weight:normal;white-space:nowrap;">Accreditation By: {{ $accByEn }}</div>

    <div class="mask" style="left:70px;top:710px;width:360px;height:30px;background:#FAF9F4;"></div>
    <div class="field en" style="left:74px;top:720px;width:360px;font-size:11px;font-weight:normal;white-space:nowrap;">Accreditation Number: [ {{ $acc }} ]</div>

    {{-- Debug: set background-color:red on masks/fields below to highlight values --}}
    <div class="mask" style="left:1008px;top:586px;width:44px;height:26px;background:#FAF9F4;"></div>
    <div class="field en" style="left:1008px;top:594px;width:44px;font-size:11px;font-weight:normal;text-align:right;">[ {{ $hours }} ]</div>

    <div class="mask" style="left:820px;top:612px;width:315px;height:28px;background:#FAF9F4;"></div>
    <div class="field en" style="left:900px;top:622px;width:236px;font-size:10px;font-weight:normal;text-align:right;">[ {{ $start }} ] – [ {{ $end }} ]</div>

    <div class="mask" style="left:960px;top:644px;width:158px;height:28px;background:#FAF9F4;"></div>
    <div class="field en" style="left:990px;top:654px;width:128px;font-size:11px;font-weight:normal;text-align:right;">[ {{ $issue }} ]</div>

    <div class="mask" style="left:820px;top:676px;width:420px;height:28px;background:#FAF9F4;"></div>
    <div class="field ar" style="left:1080px;top:684px;width:135px;font-size:12px;font-weight:bold;text-align:right;">الدورة معتمدة من</div>
    <div class="field en" style="left:1070px;top:685px;width:12px;font-size:12px;">:</div>
    <div class="field ar" style="left:820px;top:686px;width:245px;font-size:{{ $accByArSize }};font-weight:normal;text-align:right;">{{ $accByAr }}</div>

    <div class="mask" style="left:960px;top:708px;width:280px;height:34px;background:#FAF9F4;"></div>
    <div class="field ar" style="left:1110px;top:718px;width:105px;font-size:13px;font-weight:bold;text-align:right;">رقم الاعتماد</div>
    <div class="field en" style="left:1100px;top:717px;width:12px;font-size:13px;">:</div>
    <div class="field en" style="left:990px;top:718px;width:100px;font-size:11px;font-weight:normal;text-align:right;">{{ $acc }}</div>

    @if (!empty($qrHtml))
        <div class="qr-box">{!! $qrHtml !!}</div>
    @endif
</div>
</body>
</html>
