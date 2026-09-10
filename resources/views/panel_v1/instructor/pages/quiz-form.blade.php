@extends('panel_v1.instructor.layouts.app')

@section('content')
<section class="pt-10 pb-16">
    <div class="container max-w-3xl">
        <div class="mb-14">
            <h1 class="font-extrabold text-36px text-primary mb-3">
                {{ !empty($quiz) ? 'تعديل الاختبار' : 'اختبار جديد' }}</h1>
        </div>

        <form method="POST"
            action="{{ !empty($quiz) ? route('panel.v1.instructor.quizzes.update', ['id' => $quiz->id]) : route('panel.v1.instructor.quizzes.store') }}"
            class="border border-d9 rounded-20px bg-white px-8 py-10 space-y-7">
            @csrf

            @if (empty($quiz))
                <div class="relative">
                    <label class="absolute -top-2.5 start-4 z-[1] bg-white px-2 font-medium text-13px text-gray">الدورة</label>
                    <select name="webinar_id"
                        class="select select-bordered w-full h-14 rounded-10px border-d9 font-medium text-16px text-black focus:outline-none focus:border-primary">
                        @foreach ($webinars ?? [] as $webinar)
                            <option value="{{ $webinar['id'] }}">{{ $webinar['title'] }}</option>
                        @endforeach
                    </select>
                </div>
            @endif

            <div class="relative">
                <label class="absolute -top-2.5 start-4 z-[1] bg-white px-2 font-medium text-13px text-gray">عنوان الاختبار *</label>
                <input type="text" name="title" value="{{ old('title', $quiz->title ?? '') }}"
                    class="input input-bordered w-full h-14 rounded-10px border-d9 font-medium text-16px text-black focus:outline-none focus:border-primary">
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-3 gap-5">
                <div class="relative">
                    <label class="absolute -top-2.5 start-4 z-[1] bg-white px-2 font-medium text-13px text-gray">درجة النجاح *</label>
                    <input type="number" name="pass_mark" value="{{ old('pass_mark', $quiz->pass_mark ?? 50) }}" min="0"
                        class="input input-bordered w-full h-14 rounded-10px border-d9 font-medium text-16px text-black focus:outline-none focus:border-primary">
                </div>
                <div class="relative">
                    <label class="absolute -top-2.5 start-4 z-[1] bg-white px-2 font-medium text-13px text-gray">المدة (دقيقة)</label>
                    <input type="number" name="time" value="{{ old('time', $quiz->time ?? '') }}" min="0"
                        class="input input-bordered w-full h-14 rounded-10px border-d9 font-medium text-16px text-black focus:outline-none focus:border-primary">
                </div>
                <div class="relative">
                    <label class="absolute -top-2.5 start-4 z-[1] bg-white px-2 font-medium text-13px text-gray">المحاولات</label>
                    <input type="number" name="attempt" value="{{ old('attempt', $quiz->attempt ?? '') }}" min="1"
                        class="input input-bordered w-full h-14 rounded-10px border-d9 font-medium text-16px text-black focus:outline-none focus:border-primary">
                </div>
            </div>

            <div class="relative">
                <label class="absolute -top-2.5 start-4 z-[1] bg-white px-2 font-medium text-13px text-gray">الحالة</label>
                <select name="status"
                    class="select select-bordered w-full h-14 rounded-10px border-d9 font-medium text-16px text-black focus:outline-none focus:border-primary">
                    <option value="active" {{ old('status', $quiz->status ?? 'active') === 'active' ? 'selected' : '' }}>نشط</option>
                    <option value="inactive" {{ old('status', $quiz->status ?? '') === 'inactive' ? 'selected' : '' }}>معطل</option>
                </select>
            </div>

            <button type="submit" class="btn btn-primary rounded-10px h-14 px-10 font-bold text-18px">
                {{ !empty($quiz) ? 'حفظ التعديلات' : 'إنشاء الاختبار' }}
            </button>
        </form>
    </div>
</section>
@endsection
