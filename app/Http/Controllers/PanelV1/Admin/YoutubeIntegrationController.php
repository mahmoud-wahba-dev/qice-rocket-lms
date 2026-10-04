<?php

namespace App\Http\Controllers\PanelV1\Admin;

use App\Http\Controllers\PanelV1\AdminController;
use App\Http\Controllers\PanelV1\Support\AdminMockData;
use App\Models\YoutubeIntegration;
use App\Services\Youtube\YoutubeOAuthService;
use Illuminate\Http\Request;

class YoutubeIntegrationController extends AdminController
{
    public function show(Request $request, YoutubeOAuthService $oauth)
    {
        $user = $this->resolveAdmin($request);
        if ($user instanceof \Illuminate\Http\RedirectResponse) {
            return $user;
        }

        $integration = YoutubeIntegration::current();

        return $this->renderAdmin(
            $request,
            'panel_v1.admin.pages.system.youtube-integration',
            'ربط بث الفيديو',
            array_merge(AdminMockData::shell('system', 'settings'), [
                'pageTitleText' => 'ربط بث الفيديو',
                'hubUrl' => route('panel.v1.admin.system.section', ['section' => 'settings']),
                'oauthConfigured' => $oauth->isConfigured(),
                'integration' => $integration,
                'connected' => $integration && $integration->isConnected(),
                'connectUrl' => route('panel.v1.admin.system.youtube.connect'),
                'disconnectUrl' => route('panel.v1.admin.system.youtube.disconnect'),
            ])
        );
    }

    public function connect(Request $request, YoutubeOAuthService $oauth)
    {
        $user = $this->resolveAdmin($request);
        if ($user instanceof \Illuminate\Http\RedirectResponse) {
            return $user;
        }

        if (!$oauth->isConfigured()) {
            return redirect()
                ->route('panel.v1.admin.system.youtube.show')
                ->with('toast', [
                    'title' => 'غير جاهز',
                    'msg' => 'أضف YOUTUBE_CLIENT_ID و YOUTUBE_CLIENT_SECRET في ملف البيئة أولاً.',
                    'type' => 'error',
                ]);
        }

        $state = $oauth->makeState();
        $request->session()->put('youtube_oauth_state', $state);

        return redirect()->away($oauth->authorizationUrl($state));
    }

    public function callback(Request $request, YoutubeOAuthService $oauth)
    {
        $user = $this->resolveAdmin($request);
        if ($user instanceof \Illuminate\Http\RedirectResponse) {
            return $user;
        }

        $expected = (string) $request->session()->pull('youtube_oauth_state', '');
        $state = (string) $request->query('state', '');
        if ($expected === '' || !hash_equals($expected, $state)) {
            return redirect()
                ->route('panel.v1.admin.system.youtube.show')
                ->with('toast', [
                    'title' => 'فشل الربط',
                    'msg' => 'انتهت صلاحية جلسة الربط. حاول مرة أخرى.',
                    'type' => 'error',
                ]);
        }

        if ($request->filled('error')) {
            return redirect()
                ->route('panel.v1.admin.system.youtube.show')
                ->with('toast', [
                    'title' => 'تم الإلغاء',
                    'msg' => 'لم تكتمل موافقة الحساب.',
                    'type' => 'error',
                ]);
        }

        try {
            $oauth->connectFromCode((string) $request->query('code'), (int) $user->id);
        } catch (\Throwable $e) {
            return redirect()
                ->route('panel.v1.admin.system.youtube.show')
                ->with('toast', [
                    'title' => 'فشل الربط',
                    'msg' => $e->getMessage(),
                    'type' => 'error',
                ]);
        }

        return redirect()
            ->route('panel.v1.admin.system.youtube.show')
            ->with('toast', [
                'title' => 'تم',
                'msg' => 'تم ربط حساب بث الفيديو بنجاح.',
                'type' => 'success',
            ]);
    }

    public function disconnect(Request $request)
    {
        $user = $this->resolveAdmin($request);
        if ($user instanceof \Illuminate\Http\RedirectResponse) {
            return $user;
        }

        YoutubeIntegration::query()->delete();

        return redirect()
            ->route('panel.v1.admin.system.youtube.show')
            ->with('toast', [
                'title' => 'تم',
                'msg' => 'تم فصل حساب بث الفيديو.',
                'type' => 'success',
            ]);
    }
}
