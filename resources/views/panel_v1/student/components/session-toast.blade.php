{{-- Flash session toast + validation errors via landing showCartToast --}}
@php
    $flashToast = session('toast');
    $validationError = $errors->any() ? $errors->first() : null;
@endphp

@if (!empty($flashToast) || !empty($validationError))
    @push('scripts')
        <script>
            document.addEventListener('DOMContentLoaded', function () {
                if (typeof window.showCartToast !== 'function') {
                    return;
                }
                @if (!empty($flashToast))
                    window.showCartToast(
                        @json($flashToast['title'] ?? 'تنبيه'),
                        @json($flashToast['msg'] ?? ''),
                        @json($flashToast['type'] ?? 'success')
                    );
                @elseif (!empty($validationError))
                    window.showCartToast('خطأ', @json($validationError), 'error');
                @endif
            });
        </script>
    @endpush
@endif
