@extends('panel_v1.admin.layouts.app')

@section('content')
<div class="space-y-6 sm:space-y-8 pb-8">
    @component('panel_v1.admin.components.page-header', [
        'title' => $pageTitleText ?? 'ربط بث الفيديو',
        'subtitle' => 'اربط قناة المنصة مرة واحدة — بعدها رفع فيديوهات المنهج من الداشبورد يُعالَج تلقائياً في الخلفية',
    ])
        @slot('actions')
            <a href="{{ $hubUrl ?? route('panel.v1.admin.system.section', ['section' => 'settings']) }}"
                class="inline-flex items-center gap-2 h-12 px-4 rounded-12px border border-d9 bg-white font-semibold text-14px text-primary hover:bg-[#FAFAF4] transition">
                <span class="icon-[tabler--arrow-right] size-4"></span>
                العودة للإعدادات
            </a>
        @endslot
    @endcomponent

    <div class="rounded-14px border border-d9 bg-white shadow-sm p-5 sm:p-6 space-y-5 max-w-2xl">
        @if (!($oauthConfigured ?? false))
            <div class="rounded-12px border border-amber-200 bg-amber-50 px-4 py-3 text-14px text-amber-900">
                أضف في ملف البيئة على السيرفر:
                <code class="font-mono text-13px">YOUTUBE_CLIENT_ID</code>،
                <code class="font-mono text-13px">YOUTUBE_CLIENT_SECRET</code>،
                واختيارياً
                <code class="font-mono text-13px">YOUTUBE_REDIRECT_URI</code>
                ثم أعد تحميل الصفحة.
            </div>
        @endif

        <div class="flex flex-wrap items-center gap-3">
            <span class="font-semibold text-14px text-primary">الحالة:</span>
            @if ($connected ?? false)
                <span class="inline-flex items-center gap-1.5 h-9 px-3 rounded-full bg-emerald-50 text-emerald-800 font-bold text-13px">
                    <span class="icon-[tabler--circle-check-filled] size-4"></span>
                    متصل
                </span>
            @else
                <span class="inline-flex items-center gap-1.5 h-9 px-3 rounded-full bg-gray-100 text-gray font-bold text-13px">
                    غير متصل
                </span>
            @endif
        </div>

        @if ($connected ?? false)
            <dl class="grid grid-cols-1 sm:grid-cols-2 gap-4 text-14px">
                <div>
                    <dt class="font-medium text-gray mb-1">القناة</dt>
                    <dd class="font-bold text-primary">{{ $integration->channel_title ?? '—' }}</dd>
                </div>
                <div>
                    <dt class="font-medium text-gray mb-1">معرّف القناة</dt>
                    <dd class="font-mono text-13px text-primary break-all">{{ $integration->channel_id ?? '—' }}</dd>
                </div>
            </dl>
        @endif

        <div class="flex flex-wrap gap-3 pt-2">
            @if ($oauthConfigured ?? false)
                <a href="{{ $connectUrl }}"
                    class="inline-flex items-center justify-center gap-2 h-12 px-5 rounded-12px bg-primary text-white font-bold text-14px hover:opacity-90 transition">
                    <span class="icon-[tabler--link] size-5"></span>
                    {{ ($connected ?? false) ? 'إعادة الربط' : 'ربط حساب بث الفيديو' }}
                </a>
            @endif

            @if ($connected ?? false)
                <form method="POST" action="{{ $disconnectUrl }}" onsubmit="return confirm('فصل الحساب؟ لن تُعالَج فيديوهات البث حتى إعادة الربط.');">
                    @csrf
                    <button type="submit"
                        class="inline-flex items-center justify-center gap-2 h-12 px-5 rounded-12px border border-d9 bg-white text-primary font-bold text-14px hover:bg-[#FAFAF4] transition">
                        فصل الحساب
                    </button>
                </form>
            @endif
        </div>

        <p class="font-medium text-13px text-gray leading-relaxed">
            بعد الربط: من منهج الدورة اختر «بث عبر المنصة» عند رفع فيديو — المعالجة تتم في الخلفية والطالب يشاهد داخل مشغّل المنصة دون واجهة خارجية ظاهرة.
        </p>
    </div>
</div>
@endsection
