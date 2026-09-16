@extends('panel_v1.admin.layouts.app')

@section('content')
<div class="space-y-6 pb-8 max-w-3xl mx-auto">
    @include('panel_v1.admin.components.page-header', [
        'title' => 'إضافة مستخدم جديد',
        'subtitle' => 'منطق حرفي من Admin\UserController::store — إنشاء مستخدم مع دور ومجموعة',
    ])

    <div class="border border-d9 rounded-14px bg-white p-6 sm:p-8">
        <form method="POST" action="{{ $formAction }}" class="space-y-5">
            @csrf
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div class="sm:col-span-2">
                    <label class="font-semibold text-14px text-primary mb-2 block">الاسم الكامل *</label>
                    <input type="text" name="full_name" value="{{ old('full_name') }}" required
                        class="input input-bordered w-full h-12 rounded-12px border-d9 text-14px" placeholder="مثال: أحمد محمد">
                    @error('full_name')<p class="text-red-500 text-12px mt-1">{{ $message }}</p>@enderror
                </div>

                <div class="sm:col-span-2">
                    <label class="font-semibold text-14px text-primary mb-2 block">البريد أو الجوال *</label>
                    <input type="text" name="email_or_mobile" value="{{ old('email_or_mobile') }}" required
                        class="input input-bordered w-full h-12 rounded-12px border-d9 text-14px" placeholder="email@example.com أو 05xxxxxxxx">
                    @error('email_or_mobile')<p class="text-red-500 text-12px mt-1">{{ $message }}</p>@enderror
                </div>

                <div>
                    <label class="font-semibold text-14px text-primary mb-2 block">الدور *</label>
                    <select name="role_id" required class="select select-bordered w-full h-12 rounded-12px border-d9 text-14px">
                        <option value="">اختر دور</option>
                        @foreach ($roles as $role)
                            <option value="{{ $role->id }}" @selected(old('role_id')==$role->id)>{{ $role->caption ?? $role->name }} (#{{ $role->id }})</option>
                        @endforeach
                    </select>
                    @error('role_id')<p class="text-red-500 text-12px mt-1">{{ $message }}</p>@enderror
                </div>

                <div>
                    <label class="font-semibold text-14px text-primary mb-2 block">المجموعة</label>
                    <select name="group_id" class="select select-bordered w-full h-12 rounded-12px border-d9 text-14px">
                        <option value="">بدون مجموعة</option>
                        @foreach ($userGroups as $g)
                            <option value="{{ $g->id }}" @selected(old('group_id')==$g->id)>{{ $g->name }}</option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="font-semibold text-14px text-primary mb-2 block">كلمة المرور *</label>
                    <input type="password" name="password" required
                        class="input input-bordered w-full h-12 rounded-12px border-d9 text-14px" placeholder="6 أحرف على الأقل">
                    @error('password')<p class="text-red-500 text-12px mt-1">{{ $message }}</p>@enderror
                </div>

                <div>
                    <label class="font-semibold text-14px text-primary mb-2 block">الحالة *</label>
                    <select name="status" required class="select select-bordered w-full h-12 rounded-12px border-d9 text-14px">
                        <option value="active" selected>نشط</option>
                        <option value="inactive">غير نشط</option>
                    </select>
                </div>
            </div>

            <div class="flex items-center gap-3 pt-4 border-t border-d9">
                <button type="submit" class="inline-flex items-center justify-center h-12 px-8 rounded-12px bg-primary text-white font-bold text-14px hover:opacity-95 transition">إنشاء المستخدم</button>
                <a href="{{ route('panel.v1.admin.system.home') }}" class="inline-flex items-center justify-center h-12 px-6 rounded-12px border border-d9 bg-white font-semibold text-14px text-primary hover:bg-[#FAFAF4] transition">إلغاء</a>
            </div>
        </form>
        <p class="font-medium text-12px text-gray mt-6 leading-relaxed">* تنفيذ حرفي من <code>Admin\UserController::store:473</code> مع <code>email_or_mobile</code> و <code>GroupUser</code> — نفس الثيم.</p>
    </div>
</div>
@endsection
