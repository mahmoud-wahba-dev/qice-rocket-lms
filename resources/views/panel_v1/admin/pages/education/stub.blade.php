@extends('panel_v1.admin.layouts.app')

@section('content')
<div class="space-y-6 pb-8">
    @include('panel_v1.admin.components.empty-stub', [
        'title' => $stubTitle ?? 'قريباً',
        'subtitle' => $stubSubtitle ?? '',
    ])
</div>
@endsection
