<?php

namespace App\Http\Controllers\PanelV1\Admin;

use App\Http\Controllers\PanelV1\AdminController;
use App\Http\Controllers\PanelV1\Support\AdminMockData;
use App\Services\GoogleDrive\GoogleDriveClient;
use Illuminate\Http\Request;

class GoogleDriveSettingsController extends AdminController
{
    public function show(Request $request, GoogleDriveClient $drive)
    {
        $user = $this->resolveAdmin($request);
        if ($user instanceof \Illuminate\Http\RedirectResponse) {
            return $user;
        }

        return $this->renderAdmin(
            $request,
            'panel_v1.admin.pages.system.google-drive',
            'Google Drive',
            $this->pageData($drive)
        );
    }

    public function saveFolder(Request $request, GoogleDriveClient $drive)
    {
        $user = $this->resolveAdmin($request);
        if ($user instanceof \Illuminate\Http\RedirectResponse) {
            return $user;
        }

        $request->validate([
            'folder' => 'required|string|max:2000',
        ]);

        try {
            $drive->saveFolderId((string) $request->input('folder'));
        } catch (\Throwable $e) {
            return redirect()
                ->route('panel.v1.admin.system.google-drive.show')
                ->with('toast', ['title' => 'خطأ', 'msg' => $e->getMessage(), 'type' => 'error']);
        }

        return redirect()
            ->route('panel.v1.admin.system.google-drive.show')
            ->with('toast', ['title' => 'تم', 'msg' => 'تم حفظ مجلد Google Drive', 'type' => 'success']);
    }

    public function uploadCredentials(Request $request, GoogleDriveClient $drive)
    {
        $user = $this->resolveAdmin($request);
        if ($user instanceof \Illuminate\Http\RedirectResponse) {
            return $user;
        }

        $request->validate([
            'credentials' => 'required|file|mimes:json,txt|max:2048',
        ]);

        try {
            $email = $drive->storeCredentialsUpload($request->file('credentials'));
        } catch (\Throwable $e) {
            return redirect()
                ->route('panel.v1.admin.system.google-drive.show')
                ->with('toast', ['title' => 'خطأ', 'msg' => $e->getMessage(), 'type' => 'error']);
        }

        return redirect()
            ->route('panel.v1.admin.system.google-drive.show')
            ->with('toast', [
                'title' => 'تم',
                'msg' => 'تم رفع مفتاح الاتصال. شارك مجلد Drive مع: ' . $email,
                'type' => 'success',
            ]);
    }

    private function pageData(GoogleDriveClient $drive): array
    {
        $hasCreds = (bool) $drive->credentialsPath();
        $hasFolder = $drive->folderId() !== '';
        $configured = $drive->isConfigured();

        return array_merge(AdminMockData::shell('system', 'settings'), [
            'pageTitleText' => 'ربط فيديوهات Google Drive',
            'hubUrl' => route('panel.v1.admin.system.section', ['section' => 'settings']),
            'configured' => $configured,
            'hasCredentials' => $hasCreds,
            'hasFolder' => $hasFolder,
            'folderId' => $drive->folderId(),
            'folderUrl' => $drive->folderUrl(),
            'serviceEmail' => $drive->serviceAccountEmail(),
            'saveFolderUrl' => route('panel.v1.admin.system.google-drive.folder'),
            'uploadCredentialsUrl' => route('panel.v1.admin.system.google-drive.credentials'),
        ]);
    }
}
