@extends('panel_v1.admin.layouts.app')
@section('content')
<div class="space-y-6 pb-8 max-w-3xl mx-auto">
    @include('panel_v1.admin.components.page-header', ['title'=>'تعديل مستخدم','subtitle'=>$editUser->full_name.' (#'.$editUser->id.')'])
    <div class="border border-d9 rounded-14px bg-white p-6 sm:p-8">
        <form method="POST" action="{{ $formAction }}" class="space-y-5">
            @csrf
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div class="sm:col-span-2">
                    <label class="font-semibold text-14px text-primary mb-2 block">الاسم الكامل *</label>
                    <input type="text" name="full_name" value="{{ old('full_name', $editUser->full_name) }}" required class="input input-bordered w-full h-12 rounded-12px border-d9 text-14px focus:border-primary focus:outline-none">
                    @error('full_name')<p class="text-red-500 text-12px mt-1">{{ $message }}</p>@enderror
                </div>
                <div>
                    <label class="font-semibold text-14px text-primary mb-2 block">البريد</label>
                    <input type="email" name="email" value="{{ old('email', $editUser->email) }}" class="input input-bordered w-full h-12 rounded-12px border-d9 text-14px" dir="ltr">
                    @error('email')<p class="text-red-500 text-12px mt-1">{{ $message }}</p>@enderror
                </div>
                <div>
                    <label class="font-semibold text-14px text-primary mb-2 block">الجوال</label>
                    <input type="text" name="mobile" value="{{ old('mobile', $editUser->mobile) }}" class="input input-bordered w-full h-12 rounded-12px border-d9 text-14px" dir="ltr">
                    @error('mobile')<p class="text-red-500 text-12px mt-1">{{ $message }}</p>@enderror
                </div>
                <div>
                    <label class="font-semibold text-14px text-primary mb-2 block">كلمة مرور جديدة</label>
                    <input type="password" name="password" class="input input-bordered w-full h-12 rounded-12px border-d9 text-14px" placeholder="اتركها فارغة للإبقاء">
                    @error('password')<p class="text-red-500 text-12px mt-1">{{ $message }}</p>@enderror
                </div>
                <div>
                    <label class="font-semibold text-14px text-primary mb-2 block">الحالة *</label>
                    <select name="status" required class="select select-bordered w-full h-12 rounded-12px border-d9 text-14px bg-white">
                        <option value="active" @selected(old('status', $editUser->status)=='active')>نشط</option>
                        <option value="inactive" @selected(old('status', $editUser->status)=='inactive')>غير نشط</option>
                    </select>
                </div>
                <div>
                    <label class="font-semibold text-14px text-primary mb-2 block">الدور</label>
                    <select name="role_id" class="select select-bordered w-full h-12 rounded-12px border-d9 text-14px bg-white">
                        <option value="">بدون تغيير</option>
                        @foreach ($roles as $role)
                            <option value="{{ $role->id }}" @selected(old('role_id')==$role->id)>{{ $role->caption ?? $role->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="sm:col-span-2 grid grid-cols-1 sm:grid-cols-3 gap-3 items-end">
                    <label class="flex items-center gap-2 font-medium text-13px text-primary"><input type="checkbox" name="ban" value="1" @checked(old('ban', $editUser->ban)) class="checkbox checkbox-sm"> حظر</label>
                    <div>
                        <label class="font-semibold text-13px text-primary mb-1 block">بداية الحظر</label>
                        <input type="date" name="ban_start_at" value="{{ $editUser->ban_start_at ? date('Y-m-d', (int)$editUser->ban_start_at) : '' }}" class="input input-bordered w-full h-11 rounded-10px border-d9 text-13px">
                    </div>
                    <div>
                        <label class="font-semibold text-13px text-primary mb-1 block">نهاية الحظر</label>
                        <input type="date" name="ban_end_at" value="{{ $editUser->ban_end_at ? date('Y-m-d', (int)$editUser->ban_end_at) : '' }}" class="input input-bordered w-full h-11 rounded-10px border-d9 text-13px">
                    </div>
                </div>
            </div>
            <div class="flex items-center gap-3 pt-4 border-t border-d9">
                <button type="submit" class="inline-flex items-center justify-center h-12 px-8 rounded-12px bg-primary text-white font-bold text-14px hover:opacity-95 transition">حفظ التعديلات</button>
                <a href="{{ route('panel.v1.admin.system.home') }}" class="inline-flex items-center justify-center h-12 px-6 rounded-12px border border-d9 bg-white font-semibold text-14px text-primary hover:bg-[#FAFAF4] transition">إلغاء</a>
            </div>
        </form>
        <p class="font-medium text-12px text-gray mt-6 leading-relaxed">* مقتطف مبسط من <code>Admin\UserController::update:874</code> (عام + حظر) — نفس الثيم.</p>
    </div>
</div>
@endsection
