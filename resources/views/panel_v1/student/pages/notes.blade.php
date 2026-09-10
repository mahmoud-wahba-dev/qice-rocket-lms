@extends('panel_v1.student.layouts.app')

@section('content')
<section class="pt-10 pb-16">
    <div class="container max-w-3xl">
        <div class="mb-14">
            <h1 class="font-extrabold text-36px text-primary mb-3">ملاحظاتي</h1>
            <p class="font-semibold text-20px text-gray">ملاحظاتك الشخصية على الدورات</p>
        </div>

        <form method="POST" action="{{ route('panel.v1.student.notes.store') }}"
            class="border border-d9 rounded-16px bg-white px-8 py-8 mb-10 space-y-5">
            @csrf
            <div class="relative">
                <label class="absolute -top-2.5 start-4 z-[1] bg-white px-2 font-medium text-13px text-gray">الدورة *</label>
                <select name="webinar_id"
                    class="select select-bordered w-full h-14 rounded-10px border-d9 font-medium text-16px text-black focus:outline-none focus:border-primary">
                    @foreach ($enrolledForNotes ?? [] as $webinar)
                        <option value="{{ $webinar->id }}">{{ $webinar->title }}</option>
                    @endforeach
                </select>
            </div>
            <div class="relative">
                <label class="absolute -top-2.5 start-4 z-[1] bg-white px-2 font-medium text-13px text-gray">الملاحظة *</label>
                <textarea name="note" rows="4" required maxlength="5000"
                    class="textarea textarea-bordered w-full rounded-10px border-d9 font-medium text-16px text-black focus:outline-none focus:border-primary min-h-32"></textarea>
            </div>
            <button type="submit" class="btn btn-primary rounded-10px h-12 px-8 font-bold text-16px">حفظ الملاحظة</button>
        </form>

        <div class="space-y-4">
            @forelse ($notes ?? [] as $note)
                <div class="border border-d9 rounded-16px bg-white px-8 py-6">
                    <div class="flex items-start justify-between gap-3 mb-2">
                        <p class="font-bold text-16px text-primary">دورة #{{ $note->course_id }}</p>
                        <form method="POST" action="{{ route('panel.v1.student.notes.delete', ['id' => $note->id]) }}"
                            onsubmit="return confirm('حذف الملاحظة؟');">
                            @csrf
                            <button type="submit" class="font-bold text-14px text-[#EF4444]">حذف</button>
                        </form>
                    </div>
                    <p class="font-medium text-15px text-black leading-relaxed">{{ $note->note }}</p>
                    <p class="font-medium text-12px text-gray mt-2">{{ date('Y/m/d', (int) $note->created_at) }}</p>
                </div>
            @empty
                <div class="border border-d9 rounded-16px bg-white px-8 py-20 center flex-col text-center">
                    <p class="font-semibold text-24px text-gray">لا توجد ملاحظات بعد</p>
                </div>
            @endforelse
        </div>
    </div>
</section>
@endsection
