@extends('panel_v1.admin.layouts.app')

@section('content')
@php
    $configured = !empty($configured);
    $hasCredentials = !empty($hasCredentials);
    $hasFolder = !empty($hasFolder);
    $step = !$hasCredentials ? 1 : (!$hasFolder ? 2 : 3);
@endphp
<div class="space-y-6 sm:space-y-8 pb-8">
    @component('panel_v1.admin.components.page-header', [
        'title' => $pageTitleText ?? 'ربط فيديوهات Google Drive',
        'subtitle' => 'اتبع الخطوات بالترتيب — بعد الإعداد ارفع الفيديوهات على Drive والصق الرابط داخل منهج الدورة',
    ])
        @slot('actions')
            <a href="{{ $hubUrl ?? route('panel.v1.admin.system.section', ['section' => 'settings']) }}"
                class="inline-flex items-center gap-2 h-12 px-4 rounded-12px border border-d9 bg-white font-semibold text-14px text-primary hover:bg-[#FAFAF4] transition">
                <span class="icon-[tabler--arrow-right] size-4"></span>
                العودة للإعدادات
            </a>
        @endslot
    @endcomponent

    <div class="flex flex-wrap items-center gap-3">
        <span class="font-semibold text-14px text-primary">الحالة:</span>
        @if ($configured)
            <span class="inline-flex items-center gap-1.5 h-9 px-3 rounded-full bg-emerald-50 text-emerald-800 font-bold text-13px">
                <span class="icon-[tabler--circle-check-filled] size-4"></span>
                جاهز للاستخدام
            </span>
        @else
            <span class="inline-flex items-center gap-1.5 h-9 px-3 rounded-full bg-amber-50 text-amber-900 font-bold text-13px">
                أكمل الخطوات أدناه ({{ $step }} من 3)
            </span>
        @endif
    </div>

    {{-- Step 1 --}}
    <section class="rounded-14px border border-d9 bg-white shadow-sm p-5 sm:p-6 space-y-4 max-w-3xl {{ $step === 1 ? 'ring-2 ring-primary/20' : '' }}">
        <div class="flex items-start gap-3">
            <span class="size-9 shrink-0 rounded-full {{ $hasCredentials ? 'bg-emerald-100 text-emerald-800' : 'bg-primary text-white' }} center font-bold text-14px">1</span>
            <div class="min-w-0 flex-1">
                <h2 class="font-bold text-18px text-primary mb-1">ارفع مفتاح الاتصال (مرة واحدة)</h2>
                <p class="font-medium text-13px text-gray leading-relaxed mb-3">
                    من Google Cloud أنشئ Service Account وفعّل Drive API ثم حمّل ملف JSON هنا.
                    هذا الملف يسمح للموقع بقراءة فيديوهاتك الخاصة بأمان (محلي أو سيرفر — نفس الإعداد).
                </p>

                @if ($hasCredentials)
                    <div class="rounded-12px bg-emerald-50 border border-emerald-100 px-4 py-3 mb-3">
                        <p class="font-semibold text-13px text-emerald-900 mb-1">تم رفع المفتاح</p>
                        <p class="font-medium text-12px text-emerald-800 break-all">البريد للمشاركة: <span class="font-mono" data-copy-email>{{ $serviceEmail }}</span></p>
                        <button type="button" class="mt-2 inline-flex items-center gap-1.5 h-9 px-3 rounded-10px bg-white border border-emerald-200 font-semibold text-12px text-emerald-900"
                            onclick="navigator.clipboard.writeText(@json($serviceEmail)); this.textContent='تم النسخ';">
                            نسخ البريد
                        </button>
                    </div>
                @endif

                <form method="POST" action="{{ $uploadCredentialsUrl }}" enctype="multipart/form-data" class="flex flex-col sm:flex-row gap-3 items-stretch sm:items-end">
                    @csrf
                    <label class="flex-1">
                        <span class="block font-semibold text-13px text-primary mb-1.5">ملف JSON</span>
                        <input type="file" name="credentials" accept=".json,application/json" required
                            class="block w-full text-13px file:me-3 file:h-10 file:px-4 file:rounded-10px file:border-0 file:bg-primary/10 file:font-semibold file:text-primary">
                    </label>
                    <button type="submit" class="inline-flex items-center justify-center h-12 px-5 rounded-12px bg-primary text-white font-bold text-14px hover:opacity-90 transition shrink-0">
                        {{ $hasCredentials ? 'استبدال المفتاح' : 'رفع المفتاح' }}
                    </button>
                </form>
            </div>
        </div>
    </section>

    {{-- Step 2 --}}
    <section class="rounded-14px border border-d9 bg-white shadow-sm p-5 sm:p-6 space-y-4 max-w-3xl {{ $step === 2 ? 'ring-2 ring-primary/20' : '' }}">
        <div class="flex items-start gap-3">
            <span class="size-9 shrink-0 rounded-full {{ $hasFolder ? 'bg-emerald-100 text-emerald-800' : 'bg-primary text-white' }} center font-bold text-14px">2</span>
            <div class="min-w-0 flex-1">
                <h2 class="font-bold text-18px text-primary mb-1">حدد مجلد الفيديوهات</h2>
                <p class="font-medium text-13px text-gray leading-relaxed mb-3">
                    أنشئ مجلداً في Google Drive (مثال: QIEC Videos) والصق رابط المجلد هنا.
                    ثم من Drive: مشاركة → أضف البريد أعلاه كـ <strong>محرر</strong> (مهم جداً).
                </p>

                <form method="POST" action="{{ $saveFolderUrl }}" class="space-y-3">
                    @csrf
                    <label class="block">
                        <span class="block font-semibold text-13px text-primary mb-1.5">رابط المجلد أو المعرّف</span>
                        <input type="text" name="folder" value="{{ $folderId }}" required
                            placeholder="https://drive.google.com/drive/folders/...."
                            class="input input-bordered w-full h-12 rounded-10px border-d9 font-medium text-14px text-black focus:outline-none focus:border-primary">
                    </label>
                    <button type="submit" class="inline-flex items-center justify-center h-12 px-5 rounded-12px bg-primary text-white font-bold text-14px hover:opacity-90 transition">
                        حفظ المجلد
                    </button>
                </form>

                @if ($hasFolder)
                    <p class="mt-3 font-medium text-12px text-gray">المجلد الحالي: <span class="font-mono text-primary break-all">{{ $folderId }}</span></p>
                @endif
            </div>
        </div>
    </section>

    {{-- Step 3 --}}
    <section class="rounded-14px border border-d9 bg-white shadow-sm p-5 sm:p-6 space-y-4 max-w-3xl {{ $step === 3 ? 'ring-2 ring-primary/20' : '' }}">
        <div class="flex items-start gap-3">
            <span class="size-9 shrink-0 rounded-full {{ $configured ? 'bg-emerald-100 text-emerald-800' : 'bg-primary text-white' }} center font-bold text-14px">3</span>
            <div class="min-w-0 flex-1">
                <h2 class="font-bold text-18px text-primary mb-1">ارفع فيديو واربطه بالدورة</h2>
                <ol class="list-decimal pe-5 space-y-2 font-medium text-13px text-gray leading-relaxed mb-4">
                    <li>افتح المجلد من الزر أدناه وارفع الفيديو هناك</li>
                    <li>انسخ رابط الملف من Drive</li>
                    <li>في منهج الدورة اختر «Google Drive» والصق الرابط ثم اضغط إضافة</li>
                    <li>لا تفعّل مشاركة «أي شخص لديه الرابط» — اترك الملفات خاصة</li>
                </ol>

                <div class="flex flex-wrap gap-3">
                    <a href="{{ $folderUrl }}" target="_blank" rel="noopener"
                        class="inline-flex items-center justify-center gap-2 h-12 px-5 rounded-12px bg-primary text-white font-bold text-14px hover:opacity-90 transition {{ $configured ? '' : 'opacity-60 pointer-events-none' }}">
                        <span class="icon-[tabler--brand-google-drive] size-5"></span>
                        فتح مجلد Google Drive
                    </a>
                    @if ($configured)
                        <a href="{{ route('panel.v1.admin.education.home') }}"
                            class="inline-flex items-center justify-center gap-2 h-12 px-5 rounded-12px border border-d9 bg-white font-bold text-14px text-primary hover:bg-[#FAFAF4] transition">
                            الذهاب للدورات
                        </a>
                    @endif
                </div>
            </div>
        </div>
    </section>
</div>
@endsection
