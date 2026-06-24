<?php

namespace App\Http\Controllers\Web\traits;

use App\Http\Controllers\Web\CartManagerController;
use App\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

trait LandingAuthRedirectTrait
{
    protected function storeLandingAuthIntent(string $intent, array $extras = []): void
    {
        session([
            'landing_auth_intent' => $intent,
            'landing_free_slug' => $extras['slug'] ?? null,
            'landing_checkout_discount_id' => $extras['discount_id'] ?? null,
        ]);
    }

    protected function clearLandingAuthIntent(): void
    {
        session()->forget([
            'landing_auth_intent',
            'landing_free_slug',
            'landing_checkout_discount_id',
        ]);
    }

    protected function landingAuthIntentMessage(): ?string
    {
        $intent = session('landing_auth_intent');

        if ($intent === 'checkout') {
            return 'سجّل الدخول لإتمام الشراء';
        }

        if ($intent === 'free_enroll') {
            return 'سجّل الدخول للتسجيل في الدورة المجانية';
        }

        return null;
    }

    protected function resolveLandingAuthRedirect(Request $request, User $user): ?RedirectResponse
    {
        $intent = session('landing_auth_intent');
        $discountId = session('landing_checkout_discount_id');
        $freeSlug = session('landing_free_slug');

        $this->clearLandingAuthIntent();

        if ($intent === 'checkout') {
            $params = [];
            if (!empty($discountId)) {
                $params['discount_id'] = $discountId;
            }

            return redirect()->route('landing.v1.checkout', $params);
        }

        if ($intent === 'free_enroll' && !empty($freeSlug)) {
            return redirect('/course/' . $freeSlug . '/free');
        }

        $cartManager = new CartManagerController();
        $carts = $cartManager->getCarts();

        if ($carts->isNotEmpty()) {
            return redirect()->route('landing.v1.checkout');
        }

        return null;
    }
}
