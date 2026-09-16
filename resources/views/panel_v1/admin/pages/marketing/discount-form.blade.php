@extends('panel_v1.admin.layouts.app')
@section('content')
@php $isEdit = isset($discount) && $discount; @endphp
<div class="space-y-6 pb-8 max-w-3xl mx-auto">
    @include('panel_v1.admin.components.page-header', ['title'=> $isEdit ? 'تعديل قسيمة' : 'إنشاء قسيمة جديدة','subtitle'=>'منطق Admin\DiscountController حرفياً — كل الحقول مدعومة'])
    <div class="border border-d9 rounded-14px bg-white p-6 sm:p-8">
        <form method="POST" action="{{ $formAction }}" class="space-y-5">
            @csrf
            @if($isEdit)
                <div class="rounded-12px bg-[#F0FDF4] border border-[#BBF7D0] px-4 py-3 flex items-center justify-between">
                    <span class="font-mono font-bold text-primary">#{{ $discount->id }} — {{ $discount->code }}</span>
                    <span class="inline-flex rounded-full px-2.5 py-1 font-bold text-11px {{ $discount->expired_at < time() ? 'bg-[#FEE2E2] text-[#DC2626]' : 'bg-[#D1FAE5] text-[#059669]' }}">{{ $discount->expired_at < time() ? 'منتهي' : 'نشط' }}</span>
                </div>
            @endif
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div class="sm:col-span-2">
                    <label class="font-semibold text-14px text-primary mb-2 block">العنوان *</label>
                    <input type="text" name="title" value="{{ old('title', $discount->title ?? '') }}" required class="input input-bordered w-full h-12 rounded-12px border-d9 text-14px focus:border-primary focus:outline-none" placeholder="مثال: خصم التأسيس">
                    @error('title')<p class="text-red-500 text-12px mt-1">{{ $message }}</p>@enderror
                </div>
                <div>
                    <label class="font-semibold text-14px text-primary mb-2 block">الكود * <span class="font-normal text-11px text-gray">(فريد)</span></label>
                    <input type="text" name="code" value="{{ old('code', $discount->code ?? '') }}" required class="input input-bordered w-full h-12 rounded-12px border-d9 text-14px focus:border-primary focus:outline-none" placeholder="مثال: QIEC20" dir="ltr">
                    @error('code')<p class="text-red-500 text-12px mt-1">{{ $message }}</p>@enderror
                </div>
                <div>
                    <label class="font-semibold text-14px text-primary mb-2 block">تاريخ الانتهاء *</label>
                    <input type="datetime-local" name="expired_at" value="{{ old('expired_at', isset($discount) ? date('Y-m-d\TH:i', (int)$discount->expired_at) : '') }}" required class="input input-bordered w-full h-12 rounded-12px border-d9 text-14px">
                    @error('expired_at')<p class="text-red-500 text-12px mt-1">{{ $message }}</p>@enderror
                </div>
                <div>
                    <label class="font-semibold text-14px text-primary mb-2 block">نوع الخصم *</label>
                    <select name="discount_type" required class="select select-bordered w-full h-12 rounded-12px border-d9 text-14px bg-white">
                        <option value="percentage" @selected(old('discount_type', $discount->discount_type ?? 'percentage')=='percentage')>نسبة مئوية</option>
                        <option value="fixed_amount" @selected(old('discount_type', $discount->discount_type ?? '')=='fixed_amount')>مبلغ ثابت</option>
                    </select>
                </div>
                <div>
                    <label class="font-semibold text-14px text-primary mb-2 block">المصدر *</label>
                    <select name="source" required class="select select-bordered w-full h-12 rounded-12px border-d9 text-14px bg-white">
                        @foreach(['all'=>'الكل','course'=>'دورة','bundle'=>'حزمة','category'=>'تصنيف','product'=>'منتج','event'=>'فعالية','meeting'=>'اجتماع','meeting_package'=>'باقة اجتماع'] as $k=>$lbl)
                            <option value="{{ $k }}" @selected(old('source', $discount->source ?? 'all')==$k)>{{ $lbl }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="font-semibold text-14px text-primary mb-2 block">النسبة %</label>
                    <input type="number" name="percent" value="{{ old('percent', $discount->percent ?? '') }}" min="0" max="100" class="input input-bordered w-full h-12 rounded-12px border-d9 text-14px">
                </div>
                <div>
                    <label class="font-semibold text-14px text-primary mb-2 block">المبلغ الثابت</label>
                    <input type="number" step="0.01" name="amount" value="{{ old('amount', $discount->amount ?? '') }}" min="0" class="input input-bordered w-full h-12 rounded-12px border-d9 text-14px" placeholder="مثال: 50">
                </div>
                <div>
                    <label class="font-semibold text-14px text-primary mb-2 block">الحد الأقصى</label>
                    <input type="number" step="0.01" name="max_amount" value="{{ old('max_amount', $discount->max_amount ?? '') }}" min="0" class="input input-bordered w-full h-12 rounded-12px border-d9 text-14px">
                </div>
                <div>
                    <label class="font-semibold text-14px text-primary mb-2 block">الحد الأدنى للطلب</label>
                    <input type="number" step="0.01" name="minimum_order" value="{{ old('minimum_order', $discount->minimum_order ?? '') }}" min="0" class="input input-bordered w-full h-12 rounded-12px border-d9 text-14px">
                </div>
                <div>
                    <label class="font-semibold text-14px text-primary mb-2 block">العدد</label>
                    <input type="number" name="count" value="{{ old('count', $discount->count ?? 1) }}" min="1" class="input input-bordered w-full h-12 rounded-12px border-d9 text-14px">
                </div>
                <div class="sm:col-span-2 border-t border-d9 pt-4 mt-2">
                    <p class="font-bold text-14px text-primary mb-3">ربط القسيمة (مطابق لـ DiscountController::handleRelationItems)</p>
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label class="font-semibold text-13px text-primary mb-2 block">المستخدم المخصص (special_users)</label>
                            <input type="number" name="user_id" value="{{ old('user_id', $discount->discountUsers->user_id ?? '') }}" placeholder="ID المستخدم — فارغ = كل المستخدمين" class="input input-bordered w-full h-12 rounded-12px border-d9 text-14px">
                            <p class="font-medium text-11px text-gray mt-1">يعادل DiscountUser — اتركه فارغاً لـ all_users</p>
                        </div>
                        <div>
                            <label class="font-semibold text-13px text-primary mb-2 block">نوع المنتج (للمنتجات)</label>
                            <select name="product_type" class="select select-bordered w-full h-12 rounded-12px border-d9 text-14px bg-white">
                                <option value="">—</option>
                                <option value="all" @selected(old('product_type', $discount->product_type ?? '')=='all')>الكل</option>
                                <option value="physical" @selected(old('product_type', $discount->product_type ?? '')=='physical')>مادي</option>
                                <option value="virtual" @selected(old('product_type', $discount->product_type ?? '')=='virtual')>افتراضي</option>
                            </select>
                        </div>
                        <div>
                            <label class="font-semibold text-13px text-primary mb-2 block">دورات محددة</label>
                            <select name="webinar_ids[]" multiple class="select select-bordered w-full min-h-24 rounded-12px border-d9 text-13px bg-white p-2">
                                @foreach(($webinars ?? []) as $w)
                                    <option value="{{ $w['id'] }}" @selected(in_array($w['id'], old('webinar_ids', $discountCourses ?? [])))>{{ $w['title'] }}</option>
                                @endforeach
                            </select>
                            <p class="font-medium text-11px text-gray mt-1">DiscountCourse — اتركه فارغاً للكل</p>
                        </div>
                        <div>
                            <label class="font-semibold text-13px text-primary mb-2 block">حزم محددة</label>
                            <select name="bundle_ids[]" multiple class="select select-bordered w-full min-h-24 rounded-12px border-d9 text-13px bg-white p-2">
                                @foreach(($bundles ?? []) as $b)
                                    <option value="{{ $b['id'] }}" @selected(in_array($b['id'], old('bundle_ids', $discountBundles ?? [])))>{{ $b['title'] }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label class="font-semibold text-13px text-primary mb-2 block">تصنيفات</label>
                            <select name="category_ids[]" multiple class="select select-bordered w-full min-h-24 rounded-12px border-d9 text-13px bg-white p-2">
                                @foreach(($categories ?? []) as $c)
                                    <option value="{{ $c['id'] }}">{{ $c['title'] }}</option>
                                @endforeach
                            </select>
                            <p class="font-medium text-11px text-gray mt-1">DiscountCategory</p>
                        </div>
                        <div>
                            <label class="font-semibold text-13px text-primary mb-2 block">مجموعات المستخدمين</label>
                            <select name="group_ids[]" multiple class="select select-bordered w-full min-h-24 rounded-12px border-d9 text-13px bg-white p-2">
                                @foreach(($userGroups ?? []) as $g)
                                    <option value="{{ $g->id }}" @selected(in_array($g->id, old('group_ids', $discountGroupIds ?? [])))>{{ $g->name }}</option>
                                @endforeach
                            </select>
                            <p class="font-medium text-11px text-gray mt-1">DiscountGroup</p>
                        </div>
                    </div>
                </div>
                <div class="sm:col-span-2 flex items-center gap-3 p-4 rounded-12px bg-[#FAFAF4] border border-d9">
                    <label class="flex items-center gap-2 cursor-pointer">
                        <input type="checkbox" name="for_first_purchase" value="1" @checked(old('for_first_purchase', $discount->for_first_purchase ?? false)) class="checkbox checkbox-sm rounded-6px">
                        <span class="font-semibold text-13px text-primary">للشراء الأول فقط</span>
                    </label>
                    <span class="mx-2 text-d9">|</span>
                    <span class="font-medium text-12px text-gray">المصدر `all` يطبق على كل السلة — `course/bundle` يقيد حسب المحدد أعلاه</span>
                </div>
            </div>
            <div class="flex items-center gap-3 pt-4 border-t border-d9">
                <button type="submit" class="inline-flex items-center justify-center h-12 px-8 rounded-12px bg-primary text-white font-bold text-14px hover:opacity-95 transition">{{ $isEdit ? 'حفظ التعديلات' : 'إنشاء القسيمة' }}</button>
                <a href="{{ route('panel.v1.admin.marketing.section',['section'=>'discounts']) }}" class="inline-flex items-center justify-center h-12 px-6 rounded-12px border border-d9 bg-white font-semibold text-14px text-primary hover:bg-[#FAFAF4] transition">إلغاء</a>
            </div>
        </form>
    </div>
</div>
@endsection
