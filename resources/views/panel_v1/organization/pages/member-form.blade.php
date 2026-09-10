@extends('panel_v1.organization.layouts.app')

@section('content')
<section class="pt-10 pb-16">
    <div class="container max-w-3xl">
        <div class="mb-14">
            <h1 class="font-extrabold text-36px text-primary mb-3">
                {{ !empty($member) ? 'تعديل عضو' : 'عضو جديد' }}</h1>
        </div>

        <form method="POST"
            action="{{ !empty($member) ? route('panel.v1.organization.members.update', ['type' => $type, 'id' => $member->id]) : route('panel.v1.organization.members.store', ['type' => $type]) }}"
            class="border border-d9 rounded-20px bg-white px-8 py-10 space-y-7">
            @csrf

            <div class="relative">
                <label class="absolute -top-2.5 start-4 z-[1] bg-white px-2 font-medium text-13px text-gray">الاسم *</label>
                <input type="text" name="full_name" value="{{ old('full_name', $member->full_name ?? '') }}"
                    class="input input-bordered w-full h-14 rounded-10px border-d9 font-medium text-16px text-black focus:outline-none focus:border-primary">
            </div>

            <div class="relative">
                <label class="absolute -top-2.5 start-4 z-[1] bg-white px-2 font-medium text-13px text-gray">البريد الإلكتروني *</label>
                <input type="email" name="email" value="{{ old('email', $member->email ?? '') }}"
                    class="input input-bordered w-full h-14 rounded-10px border-d9 font-medium text-16px text-black focus:outline-none focus:border-primary">
            </div>

            <div class="relative">
                <label class="absolute -top-2.5 start-4 z-[1] bg-white px-2 font-medium text-13px text-gray">هاتف</label>
                <input type="text" name="mobile" value="{{ old('mobile', $member->mobile ?? '') }}"
                    class="input input-bordered w-full h-14 rounded-10px border-d9 font-medium text-16px text-black focus:outline-none focus:border-primary">
            </div>

            <div class="relative">
                <label class="absolute -top-2.5 start-4 z-[1] bg-white px-2 font-medium text-13px text-gray">كلمة المرور {{ empty($member) ? '*' : '(اتركها فارغة للإبقاء)' }}</label>
                <input type="password" name="password"
                    class="input input-bordered w-full h-14 rounded-10px border-d9 font-medium text-16px text-black focus:outline-none focus:border-primary">
            </div>

            <div class="relative">
                <label class="absolute -top-2.5 start-4 z-[1] bg-white px-2 font-medium text-13px text-gray">تأكيد كلمة المرور</label>
                <input type="password" name="password_confirmation"
                    class="input input-bordered w-full h-14 rounded-10px border-d9 font-medium text-16px text-black focus:outline-none focus:border-primary">
            </div>

            @if (!empty($member))
                <div class="relative">
                    <label class="absolute -top-2.5 start-4 z-[1] bg-white px-2 font-medium text-13px text-gray">الحالة</label>
                    <select name="status"
                        class="select select-bordered w-full h-14 rounded-10px border-d9 font-medium text-16px text-black focus:outline-none focus:border-primary">
                        <option value="active" {{ old('status', $member->status) === 'active' ? 'selected' : '' }}>نشط</option>
                        <option value="inactive" {{ old('status', $member->status) === 'inactive' ? 'selected' : '' }}>موقوف</option>
                    </select>
                </div>
            @endif

            <button type="submit" class="btn btn-primary rounded-10px h-14 px-10 font-bold text-18px">
                {{ !empty($member) ? 'حفظ التعديلات' : 'إضافة العضو' }}
            </button>
        </form>
    </div>
</section>
@endsection
