<?php

namespace App\Http\Controllers\PanelV1\Support;

use App\Bitwise\UserLevelOfTraining;
use App\Http\Controllers\Web\traits\UserFormFieldsTrait;
use App\Models\Region;
use App\Models\UserBank;
use App\Models\UserLoginHistory;
use App\Models\UserMeta;
use App\Models\UserOccupation;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

/**
 * Shared profile-settings logic for the new stacks (landing_v1 + panel_v1).
 *
 * Mirrors Panel\UserController step logic (extra_information, financial,
 * images, about, login_history) without touching design_1 code, plus:
 *  - headline write support (no endpoint existed anywhere before)
 *  - user-selected banks, regions, login histories, attachments
 */
trait ProfileSettingsTrait
{
    use UserFormFieldsTrait;

    public static function profileMediaTypes(): array
    {
        return ['avatar', 'cover_img', 'profile_secondary_image', 'profile_video', 'signature_img'];
    }

    public function saveProfileMedia(Request $request, $user): void
    {
        $updateData = [];
        $updateMeta = [];

        foreach (['avatar', 'cover_img', 'profile_secondary_image', 'profile_video'] as $name) {
            $updateData[$name] = $this->handleProfileMediaUpload($request, $user, $name);
        }

        if (!empty($request->file('signature_img'))) {
            $updateMeta['signature'] = $this->handleProfileMediaUpload($request, $user, 'signature_img');
        }

        $user->update($updateData);
        $this->saveProfileMetas($user, $updateMeta);
    }

    public function handleProfileMediaUpload(Request $request, $user, string $name)
    {
        $path = $user->{$name} ?? null;

        if (!empty($request->file($name))) {
            if (!empty($path)) {
                $this->removeFile($path);
            }

            $path = $this->uploadFile($request->file($name), 'setting', $name, $user->id);
        }

        return $path;
    }

    public function deleteProfileMedia($user, string $type): bool
    {
        if (!in_array($type, self::profileMediaTypes())) {
            return false;
        }

        if ($type === 'signature_img') {
            $user->userMetas()->where('name', 'signature')->delete();
        } else {
            $user->update([$type => null]);
        }

        return true;
    }

    public function profileExtraViewData(Request $request, $user): array
    {
        $userType = 'organization';
        if ($user->isTeacher()) {
            $userType = 'teacher';
        } elseif ($user->isUser()) {
            $userType = 'user';
        }

        return [
            'countries' => Region::query()
                ->where('type', Region::$country)
                ->orderBy('id')
                ->get(['id', 'type', 'title']),
            'formFieldsHtml' => $this->getFormFieldsByUserType($request, $userType, true, $user),
            'socials' => collect(getSocials())->sortBy('order')->toArray(),
            'userSocials' => $this->profileUserSocials($user),
        ];
    }

    /**
     * Social links are stored in user_metas.name = socials (JSON), not a users column.
     */
    public function profileUserSocials($user): array
    {
        $raw = null;

        if (!empty($user->socials)) {
            $raw = $user->socials;
        } else {
            $meta = $user->relationLoaded('userMetas')
                ? $user->userMetas->firstWhere('name', 'socials')
                : $user->userMetas()->where('name', 'socials')->first();

            $raw = $meta->value ?? null;
        }

        if (empty($raw)) {
            return [];
        }

        $decoded = is_array($raw) ? $raw : json_decode($raw, true);

        return is_array($decoded) ? $decoded : [];
    }

    public function saveProfileExtra(Request $request, $user): void
    {
        $data = $request->all();

        $update = [
            'country_id' => $data['country_id'] ?? null,
            'province_id' => $data['province_id'] ?? null,
            'city_id' => $data['city_id'] ?? null,
            'district_id' => $data['district_id'] ?? null,
            'address' => $data['address'] ?? null,
        ];

        // meeting_type is NOT NULL with default `all` — never wipe it when the form omits it
        if (!empty($data['meeting_type']) && in_array($data['meeting_type'], ['in_person', 'online', 'all'], true)) {
            $update['meeting_type'] = $data['meeting_type'];
        }

        if (!empty($data['level_of_training'])) {
            $update['level_of_training'] = (new UserLevelOfTraining())->getValue($data['level_of_training']);
        }

        if (!empty($data['latitude']) && !empty($data['longitude'])) {
            $update['location'] = DB::raw('POINT(' . (float) $data['latitude'] . ',' . (float) $data['longitude'] . ')');
        }

        $user->update($update);

        $socialsInput = $data['socials'] ?? [];
        if (is_array($socialsInput)) {
            $socialsInput = array_filter($socialsInput, static function ($value) {
                return is_string($value) ? trim($value) !== '' : !empty($value);
            });
        } else {
            $socialsInput = [];
        }

        $this->saveProfileMetas($user, [
            'birthday' => !empty($data['birthday']) ? convertTimeToUTCzone($data['birthday'])->getTimestamp() : null,
            'gender' => $data['gender'] ?? null,
            'socials' => !empty($socialsInput) ? json_encode($socialsInput, JSON_UNESCAPED_UNICODE) : null,
        ]);

        $userType = 'organization';
        if ($user->isTeacher()) {
            $userType = 'teacher';
        } elseif ($user->isUser()) {
            $userType = 'user';
        }

        if (method_exists($this, 'getFormFieldsByType')) {
            $form = $this->getFormFieldsByType($userType);
            if (!empty($form) and !empty($form->fields) and count($form->fields)) {
                $errors = $this->checkFormRequiredFields($request, $form);
                if (!count($errors)) {
                    $this->storeFormFields($request->all(), $user);
                }
            }
        }
    }

    public function profileAboutData($user): array
    {
        $userMetas = $user->userMetas;

        if (!empty($userMetas)) {
            foreach ($userMetas as $meta) {
                $user->{$meta->name} = $meta->value;
            }
        }

        return [
            'educations' => $userMetas->where('name', 'education')->values(),
            'experiences' => $userMetas->where('name', 'experience')->values(),
            'occupations' => $user->occupations->pluck('category_id')->toArray(),
            'categories' => [],
            'attachments' => $user->profileAttachments,
        ];
    }

    public function saveProfileAbout(Request $request, $user): void
    {
        $data = $request->all();

        $user->update([
            'about' => $data['about'] ?? null,
            'bio' => $data['bio'] ?? null,
            'headline' => $data['headline'] ?? null,
        ]);

        if (!$user->isUser()) {
            UserOccupation::where('user_id', $user->id)->delete();

            if (!empty($data['occupations']) and is_array($data['occupations'])) {
                foreach (array_slice($data['occupations'], 0, 10) as $categoryId) {
                    UserOccupation::create([
                        'user_id' => $user->id,
                        'category_id' => (int) $categoryId,
                    ]);
                }
            }
        }
    }

    public function profileFinancialData($user): array
    {
        return [
            'userBanks' => UserBank::query()->with(['specifications'])->orderBy('created_at', 'desc')->get(),
        ];
    }

    public function saveProfileAccountOptions(Request $request, $user): void
    {
        $user->update([
            'offline' => $request->boolean('offline'),
            'offline_message' => $request->input('offline_message', $user->offline_message),
            'newsletter' => $request->boolean('newsletter'),
            'public_message' => $request->boolean('public_message'),
            'enable_profile_statistics' => $request->boolean('enable_profile_statistics'),
            'auto_renew_subscription' => $request->boolean('auto_renew_subscription'),
        ]);
    }

    public function saveProfileFinancial(Request $request, $user): void
    {
        $data = $request->all();

        if (!empty($data['bank_id'])) {
            $this->handleProfileBankAccount($user, $data);
        }

        $user->update([
            'identity_scan' => $this->handleProfileMediaUpload($request, $user, 'identity_scan'),
            'certificate' => $this->handleProfileMediaUpload($request, $user, 'certificate'),
        ]);
    }

    private function handleProfileBankAccount($user, array $data): void
    {
        \App\Models\UserSelectedBank::query()->where('user_id', $user->id)->delete();

        $userSelectedBank = \App\Models\UserSelectedBank::query()->create([
            'user_id' => $user->id,
            'user_bank_id' => $data['bank_id'],
        ]);

        if (!empty($data['bank_specifications']) and is_array($data['bank_specifications'])) {
            foreach ($data['bank_specifications'] as $specificationId => $value) {
                if (!empty($value)) {
                    \App\Models\UserSelectedBankSpecification::query()->create([
                        'user_id' => $user->id,
                        'user_selected_bank_id' => $userSelectedBank->id,
                        'user_bank_specification_id' => $specificationId,
                        'value' => $value,
                    ]);
                }
            }
        }
    }

    public function profileLoginHistories($user)
    {
        return UserLoginHistory::query()
            ->where('user_id', $user->id)
            ->orderBy('created_at', 'desc')
            ->limit(50)
            ->get();
    }

    public function endProfileSession($user, $sessionId): bool
    {
        $session = UserLoginHistory::query()
            ->where('user_id', $user->id)
            ->where('id', $sessionId)
            ->first();

        if (empty($session)) {
            return false;
        }

        $session->update([
            'session_end_at' => time(),
            'end_session_type' => 'by_user',
        ]);

        try {
            app('session')->getHandler()->destroy($session->session_id);
        } catch (\Throwable $e) {
        }

        if ($user->logged_count > 0) {
            $user->update(['logged_count' => $user->logged_count - 1]);
        }

        return true;
    }

    private function saveProfileMetas($user, array $metas): void
    {
        foreach ($metas as $name => $value) {
            UserMeta::query()->where('user_id', $user->id)->where('name', $name)->delete();

            if (!empty($value)) {
                UserMeta::query()->create([
                    'user_id' => $user->id,
                    'name' => $name,
                    'value' => $value,
                ]);
            }
        }
    }

    public function storeProfileMeta($user, ?string $name, ?string $value): ?UserMeta
    {
        if (empty($name) or empty($value)) {
            return null;
        }

        return UserMeta::create(['user_id' => $user->id, 'name' => $name, 'value' => $value]);
    }

    public function updateProfileMeta($user, $metaId, ?string $name, ?string $value): bool
    {
        $meta = UserMeta::where('id', $metaId)->where('user_id', $user->id);

        if (!empty($name)) {
            $meta = $meta->where('name', $name);
        }

        $meta = $meta->first();

        if (empty($meta)) {
            return false;
        }

        $meta->update(['value' => $value]);

        return true;
    }

    public function deleteProfileMeta($user, $metaId): bool
    {
        $meta = UserMeta::where('id', $metaId)->where('user_id', $user->id)->first();

        if (empty($meta)) {
            return false;
        }

        $meta->delete();

        return true;
    }

    public function storeProfileAttachment(Request $request, $user)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'file_type' => 'required|string|max:32',
            'description' => 'nullable|string|max:2000',
            'attachment' => 'required|file|max:51200',
            'locale' => 'nullable|string|max:8',
        ]);

        $attachment = \App\Models\UserProfileAttachment::query()->create([
            'user_id' => $user->id,
            'file_type' => $request->input('file_type'),
            'attachment' => null,
            'created_at' => time(),
        ]);

        $this->saveProfileAttachmentExtra($request, $user, $attachment);

        return $attachment;
    }

    public function updateProfileAttachment(Request $request, $user, $attachmentId)
    {
        $attachment = \App\Models\UserProfileAttachment::where('id', $attachmentId)
            ->where('user_id', $user->id)
            ->first();

        if (empty($attachment)) {
            return null;
        }

        $request->validate([
            'title' => 'required|string|max:255',
            'file_type' => 'required|string|max:32',
            'description' => 'nullable|string|max:2000',
            'attachment' => 'nullable|file|max:51200',
            'locale' => 'nullable|string|max:8',
        ]);

        $attachment->update(['file_type' => $request->input('file_type')]);
        $this->saveProfileAttachmentExtra($request, $user, $attachment);

        return $attachment;
    }

    public function deleteProfileAttachment($user, $attachmentId): bool
    {
        $attachment = \App\Models\UserProfileAttachment::where('id', $attachmentId)
            ->where('user_id', $user->id)
            ->first();

        if (empty($attachment)) {
            return false;
        }

        if (!empty($attachment->attachment)) {
            $this->removeFile($attachment->attachment);
        }

        $attachment->delete();

        return true;
    }

    private function saveProfileAttachmentExtra(Request $request, $user, $attachment): void
    {
        $data = $request->all();

        \App\Models\Translation\UserProfileAttachmentTranslation::query()->updateOrCreate([
            'user_profile_attachment_id' => $attachment->id,
            'locale' => mb_strtolower($data['locale'] ?? app()->getLocale()),
        ], [
            'title' => $data['title'],
            'description' => $data['description'] ?? null,
        ]);

        $path = $attachment->attachment ?? null;
        $destination = "setting/attachments/{$attachment->id}";

        if (!empty($request->file('attachment'))) {
            if (!empty($path)) {
                $this->removeFile($path);
            }

            $path = $this->uploadFile($request->file('attachment'), $destination, null, $user->id);
        }

        $attachment->update(['attachment' => $path]);
    }
}
