@extends('panel_v1.admin.layouts.app')
@section('content')
<div class="space-y-6 pb-8 max-w-3xl mx-auto">
    @include('panel_v1.admin.components.page-header', ['title'=>'إرسال نشرة بريدية','subtitle'=>'منطق Admin\NewslettersController حرفياً'])
    <div class="border border-d9 rounded-14px bg-white p-6 sm:p-8">
        <form method="POST" action="{{ $formAction ?? route('panel.v1.admin.marketing.newsletters.send') }}" enctype="multipart/form-data" class="space-y-5">
            @csrf
            <div class="grid grid-cols-1 gap-4">
                <div><label class="font-semibold text-14px text-primary mb-2 block">العنوان *</label><input type="text" name="title" value="{{ old('title') }}" required class="input input-bordered w-full h-12 rounded-12px border-d9 text-14px"></div>
                <div><label class="font-semibold text-14px text-primary mb-2 block">الوصف *</label><textarea name="description" rows="4" required class="textarea textarea-bordered w-full rounded-12px border-d9 text-14px p-3">{{ old('description') }}</textarea></div>
                <div>
                    <label class="font-semibold text-14px text-primary mb-2 block">طريقة الإرسال *</label>
                    <select name="send_method" required class="select select-bordered w-full h-12 rounded-12px border-d9 text-14px bg-white">
                        <option value="send_to_all">للكل</option>
                        <option value="send_to_bcc">BCC</option>
                        <option value="send_to_excel">ملف Excel</option>
                    </select>
                </div>
                <div><label class="font-semibold text-14px text-primary mb-2 block">BCC (عند الحاجة)</label><input type="email" name="bcc_email" value="{{ old('bcc_email') }}" class="input input-bordered w-full h-12 rounded-12px border-d9 text-14px"></div>
                <div><label class="font-semibold text-14px text-primary mb-2 block">ملف Excel (عند الحاجة)</label><input type="file" name="excel" accept=".xlsx" class="file-input file-input-bordered w-full rounded-12px border-d9"></div>
            </div>
            <div class="flex items-center gap-3 pt-4 border-t border-d9">
                <button type="submit" class="inline-flex items-center justify-center h-12 px-8 rounded-12px bg-primary text-white font-bold text-14px">إرسال</button>
                <a href="{{ route('panel.v1.admin.marketing.section',['section'=>'newsletters']) }}" class="inline-flex items-center justify-center h-12 px-6 rounded-12px border border-d9 bg-white font-semibold text-14px text-primary">إلغاء</a>
            </div>
        </form>
    </div>
</div>
@endsection
