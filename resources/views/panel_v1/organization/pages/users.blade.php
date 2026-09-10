@extends('panel_v1.organization.layouts.app')

@section('content')
<section class="pt-10 pb-16">
    <div class="container">
        <div class="mb-14">
            <h1 class="font-extrabold text-36px text-primary mb-3">
                {{ $type === 'instructors' ? 'مدربو المنظمة' : 'متدربو المنظمة' }}</h1>
        </div>

        <div class="flex gap-4 mb-10">
            <a href="{{ route('panel.v1.organization.users', ['type' => 'instructors']) }}"
                class="btn {{ $type === 'instructors' ? 'btn-primary' : 'btn-outline' }} rounded-10px h-12 px-8 font-bold text-16px">المدربون</a>
            <a href="{{ route('panel.v1.organization.users', ['type' => 'students']) }}"
                class="btn {{ $type === 'students' ? 'btn-primary' : 'btn-outline' }} rounded-10px h-12 px-8 font-bold text-16px">المتدربون</a>
            <a href="{{ route('panel.v1.organization.members.create', ['type' => $type]) }}"
                class="btn btn-primary rounded-10px h-12 px-8 font-bold text-16px ms-auto">+ عضو جديد</a>
        </div>

        <div class="flex flex-col gap-4">
            @forelse ($members ?? [] as $member)
                <div class="border border-d9 rounded-16px bg-white px-8 py-5 flex items-center justify-between gap-4">
                    <div class="flex items-center gap-4 min-w-0">
                        <div class="size-14 rounded-full bg-primary/10 center shrink-0 overflow-hidden">
                            @if (!empty($member->avatar))
                                <img src="/store/{{ $member->id }}/{{ $member->avatar }}" alt="" class="size-full object-cover">
                            @else
                                <span class="font-bold text-20px text-primary">{{ mb_substr($member->full_name ?? '?', 0, 1) }}</span>
                            @endif
                        </div>
                        <div class="min-w-0">
                            <p class="font-semibold text-20px text-primary">{{ $member->full_name }}</p>
                            <p class="font-medium text-14px text-gray">{{ $member->email }}</p>
                        </div>
                    </div>
                    <div class="flex items-center gap-3 shrink-0">
                    <a href="{{ route('panel.v1.organization.members.edit', ['type' => $type, 'id' => $member->id]) }}"
                        class="font-bold text-14px px-4 py-2 rounded-8px bg-[#E8F5E9] text-[#00B31B]">
                        تعديل
                    </a>
                    <span
                        class="font-bold text-14px px-4 py-2 rounded-8px shrink-0 {{ $member->status === 'active' ? 'bg-[#E8F5E9] text-[#00B31B]' : 'bg-fa text-gray' }}">
                        {{ $member->status === 'active' ? 'نشط' : $member->status }}
                    </span>
                    </div>
                </div>
            @empty
                <div class="border border-d9 rounded-16px bg-white px-8 py-20 center flex-col text-center">
                    <p class="font-semibold text-24px text-gray">لا يوجد أعضاء بعد</p>
                </div>
            @endforelse
        </div>
    </div>
</section>
@endsection
