<table class="table w-full text-15px sm:text-16px">
    <thead>
        <tr class="border-b border-d9 text-gray bg-f9">
            <th class="px-4 sm:px-5 py-4 text-start font-semibold">الطالب</th>
            <th class="px-4 sm:px-5 py-4 text-start font-semibold">الدورة</th>
            <th class="px-4 sm:px-5 py-4 text-start font-semibold">السعر الأصلي</th>
            <th class="px-4 sm:px-5 py-4 text-start font-semibold">الخصم</th>
            <th class="px-4 sm:px-5 py-4 text-start font-semibold">المبلغ الإجمالي</th>
            <th class="px-4 sm:px-5 py-4 text-start font-semibold">صافي الدخل</th>
            <th class="px-4 sm:px-5 py-4 text-start font-semibold">النوع</th>
            <th class="px-4 sm:px-5 py-4 text-start font-semibold">التاريخ</th>
        </tr>
    </thead>
    <tbody>
        @forelse ($rows as $row)
            <tr class="border-b border-d9 last:border-0">
                <td class="px-4 sm:px-5 py-5">
                    <div class="flex items-center gap-3 min-w-48">
                        <span class="size-11 sm:size-12 rounded-full bg-primary/10 center shrink-0">
                            <span class="font-bold text-15px text-primary">{{ mb_substr($row['name'], 0, 1) }}</span>
                        </span>
                        <div class="min-w-0">
                            <p class="font-semibold text-16px sm:text-17px text-primary truncate mb-0.5">{{ $row['name'] }}</p>
                            <p class="font-medium text-13px sm:text-14px text-gray truncate">{{ $row['email'] }}</p>
                        </div>
                    </div>
                </td>
                <td class="px-4 sm:px-5 py-5 min-w-40">
                    <p class="font-semibold text-15px sm:text-16px text-primary mb-0.5">{{ $row['course'] }}</p>
                    <p class="font-medium text-13px sm:text-14px text-gray">ID{{ $row['course_id'] }}</p>
                </td>
                <td class="px-4 sm:px-5 py-5 font-semibold text-primary whitespace-nowrap">{{ $row['original_price'] }}</td>
                <td class="px-4 sm:px-5 py-5 font-medium text-gray">{{ $row['discount'] }}</td>
                <td class="px-4 sm:px-5 py-5 font-semibold text-primary whitespace-nowrap">{{ $row['total'] }}</td>
                <td class="px-4 sm:px-5 py-5 font-semibold text-primary whitespace-nowrap">{{ $row['net'] }}</td>
                <td class="px-4 sm:px-5 py-5">
                    <span class="inline-flex items-center gap-2 rounded-full bg-[#EAF6F2] px-3 py-1.5 font-semibold text-13px sm:text-14px text-primary">
                        <span class="size-2 rounded-full bg-[#0D9488] shrink-0"></span>
                        {{ $row['type'] }}
                    </span>
                </td>
                <td class="px-4 sm:px-5 py-5 whitespace-nowrap">
                    <p class="font-semibold text-15px text-primary mb-0.5">{{ $row['date'] }}</p>
                    <p class="font-medium text-13px sm:text-14px text-gray">{{ $row['time'] }}</p>
                </td>
            </tr>
        @empty
            <tr>
                <td colspan="8" class="px-4 py-16 text-center font-medium text-16px text-gray">
                    لا توجد بيانات حالياً
                </td>
            </tr>
        @endforelse
    </tbody>
</table>
