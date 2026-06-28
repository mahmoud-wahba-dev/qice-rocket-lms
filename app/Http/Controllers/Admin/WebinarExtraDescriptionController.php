<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Translation\WebinarExtraDescriptionTranslation;
use App\Models\UpcomingCourse;
use App\Models\Webinar;
use App\Models\WebinarExtraDescription;
use Illuminate\Database\QueryException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class WebinarExtraDescriptionController extends Controller
{
    public function store(Request $request)
    {
        $this->authorize('admin_webinars_edit');

        $this->validate($request, [
            'type' => 'required|in:' . implode(',', WebinarExtraDescription::$types),
            'value' => 'required',
            'webinar_id' => 'required_without:upcoming_course_id|nullable|integer|exists:webinars,id',
            'upcoming_course_id' => 'required_without:webinar_id|nullable|integer|exists:upcoming_courses,id',
        ]);

        $data = $request->all();
        $data['locale'] = mb_strtolower($data['locale'] ?? getDefaultLocale() ?: app()->getLocale());

        $parent = $this->resolveParent($data);
        if (empty($parent['creator'])) {
            return response()->json([
                'code' => 422,
                'message' => trans('public.request_failed'),
                'errors' => [
                    'webinar_id' => [trans('update.something_went_wrong')],
                ],
            ], 422);
        }

        try {
            $columnName = $parent['column'];
            $columnValue = $parent['id'];
            $creator = $parent['creator'];

            $order = WebinarExtraDescription::query()
                ->where($columnName, $columnValue)
                ->where('type', $data['type'])
                ->count() + 1;

            $webinarExtraDescription = WebinarExtraDescription::create([
                'creator_id' => $creator->id,
                'webinar_id' => $columnName === 'webinar_id' ? $columnValue : null,
                'upcoming_course_id' => $columnName === 'upcoming_course_id' ? $columnValue : null,
                'type' => $data['type'],
                'order' => $order,
                'created_at' => time(),
            ]);

            WebinarExtraDescriptionTranslation::updateOrCreate([
                'webinar_extra_description_id' => $webinarExtraDescription->id,
                'locale' => $data['locale'],
            ], [
                'value' => $data['value'],
            ]);
        } catch (QueryException $exception) {
            Log::error('webinar-extra-description store failed', [
                'message' => $exception->getMessage(),
                'webinar_id' => $data['webinar_id'] ?? null,
                'type' => $data['type'] ?? null,
            ]);

            return response()->json([
                'code' => 500,
                'message' => trans('update.something_went_wrong'),
            ], 500);
        }

        return response()->json([
            'code' => 200,
        ], 200);
    }

    private function resolveParent(array $data): array
    {
        if (!empty($data['webinar_id'])) {
            $webinar = Webinar::query()->find($data['webinar_id']);
            if (empty($webinar)) {
                return [];
            }

            $creator = $webinar->creator ?? $webinar->teacher;

            return [
                'column' => 'webinar_id',
                'id' => $webinar->id,
                'creator' => $creator,
            ];
        }

        if (!empty($data['upcoming_course_id'])) {
            $upcomingCourse = UpcomingCourse::query()->find($data['upcoming_course_id']);
            if (empty($upcomingCourse)) {
                return [];
            }

            return [
                'column' => 'upcoming_course_id',
                'id' => $upcomingCourse->id,
                'creator' => $upcomingCourse->creator,
            ];
        }

        return [];
    }

    public function edit(Request $request, $id)
    {
        $this->authorize('admin_webinars_edit');

        $webinarExtraDescription = WebinarExtraDescription::find($id);

        if (!empty($webinarExtraDescription)) {
            $locale = $request->get('locale', app()->getLocale());
            if (empty($locale)) {
                $locale = app()->getLocale();
            }
            storeContentLocale($locale, $webinarExtraDescription->getTable(), $webinarExtraDescription->id);

            $webinarExtraDescription->value = $webinarExtraDescription->getValueAttribute();
            $webinarExtraDescription->locale = mb_strtoupper($locale);

            return response()->json([
                'webinarExtraDescription' => $webinarExtraDescription,
            ], 200);
        }

        return response()->json([], 422);
    }

    public function update(Request $request, $id)
    {
        $this->authorize('admin_webinars_edit');

        $this->validate($request, [
            'type' => 'required|in:' . implode(',', WebinarExtraDescription::$types),
            'value' => 'required',
        ]);

        $data = $request->all();
        $data['locale'] = mb_strtolower($data['locale'] ?? getDefaultLocale() ?: app()->getLocale());

        $webinarExtraDescription = WebinarExtraDescription::find($id);

        if ($webinarExtraDescription) {
            WebinarExtraDescriptionTranslation::updateOrCreate([
                'webinar_extra_description_id' => $webinarExtraDescription->id,
                'locale' => $data['locale'],
            ], [
                'value' => $data['value'],
            ]);
        }

        return response()->json([
            'code' => 200,
        ], 200);
    }

    public function destroy(Request $request, $id)
    {
        $this->authorize('admin_webinars_edit');

        $webinarExtraDescription = WebinarExtraDescription::find($id);
        if (!empty($webinarExtraDescription)) {
            $webinarExtraDescription->delete();
        }

        return redirect()->back();
    }
}
