{{-- Deprecated: PaymentController now renders landing_v1.pages.payment-status --}}
@extends("design_1.web.layouts.app")

@section("content")
    <section class="container mt-96 mb-104">
        <div class="text-center py-80">
            <a href="{{ panelV1HomeUrl(auth()->user()) }}" class="btn btn-primary btn-lg">{{ trans('public.my_panel') }}</a>
        </div>
    </section>
@endsection
