<?php

namespace App\Http\Controllers\PanelV1\Support;

use App\Models\Certificate;
use App\Models\Quiz;
use App\Models\QuizzesResult;
use App\Models\Sale;
use App\Models\TimeSpentOnCourse;
use App\Models\Webinar;
use App\Models\WebinarAssignment;
use App\Models\WebinarAssignmentHistory;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;

/**
 * Shared DB-backed course performance payload for instructor + admin panels.
 */
class CoursePerformanceBuilder
{
    /**
     * @param  array{
     *   review_route?: string,
     *   assignments_fallback_url?: string,
     *   remind_route?: string,
     *   remind_params?: array
     * }  $links
     */
    public static function build(Webinar $webinar, ?Request $request = null, array $links = []): array
    {
        $webinar->loadMissing(['sessions', 'files', 'textLessons', 'category']);

        $assignmentIds = WebinarAssignment::where('webinar_id', $webinar->id)->pluck('id');
        $assignmentTotal = $assignmentIds->count();
        $quizIds = Quiz::where('webinar_id', $webinar->id)->pluck('id');

        $pendingHistories = $assignmentIds->isEmpty()
            ? collect()
            : WebinarAssignmentHistory::whereIn('assignment_id', $assignmentIds)
                ->where('status', WebinarAssignmentHistory::$pending)
                ->orderBy('id')
                ->get();

        $pendingCount = $pendingHistories->count();
        $firstPendingId = optional($pendingHistories->first())->id;

        $salesQuery = Sale::with(['buyer'])
            ->where('webinar_id', $webinar->id)
            ->whereNull('refund_at')
            ->orderByDesc('id');

        $q = trim((string) ($request?->get('q', '') ?? ''));
        if ($q !== '') {
            $salesQuery->whereHas('buyer', function ($buyerQuery) use ($q) {
                $buyerQuery->where('full_name', 'like', '%' . $q . '%')
                    ->orWhere('email', 'like', '%' . $q . '%')
                    ->orWhere('id', (int) ltrim($q, '#'));
            });
        }

        $sales = $salesQuery->limit(200)->get()->unique('buyer_id')->values();

        $reviewRoute = $links['review_route'] ?? 'panel.v1.instructor.assignments.review';
        $assignmentsFallback = $links['assignments_fallback_url']
            ?? route('panel.v1.instructor.courses.assignments', ['slug' => $webinar->slug]);
        $remindRoute = $links['remind_route'] ?? 'panel.v1.instructor.courses.performance.remind';
        $remindParams = $links['remind_params'] ?? ['slug' => $webinar->slug];

        $students = $sales->map(function ($sale) use (
            $webinar,
            $assignmentIds,
            $assignmentTotal,
            $quizIds,
            $reviewRoute,
            $assignmentsFallback,
            $remindRoute,
            $remindParams
        ) {
            $buyer = $sale->buyer;
            $buyerId = (int) $sale->buyer_id;
            $progress = self::studentProgressPercent($webinar, $buyer);

            $passedExams = $quizIds->isEmpty()
                ? 0
                : QuizzesResult::where('user_id', $buyerId)
                    ->whereIn('quiz_id', $quizIds)
                    ->where('status', QuizzesResult::$passed)
                    ->count();

            $passedAssignments = $assignmentIds->isEmpty()
                ? 0
                : WebinarAssignmentHistory::where('student_id', $buyerId)
                    ->whereIn('assignment_id', $assignmentIds)
                    ->where('status', WebinarAssignmentHistory::$passed)
                    ->count();

            $pendingAssignment = $assignmentIds->isEmpty()
                ? null
                : WebinarAssignmentHistory::where('student_id', $buyerId)
                    ->whereIn('assignment_id', $assignmentIds)
                    ->where('status', WebinarAssignmentHistory::$pending)
                    ->orderByDesc('id')
                    ->first();

            $seconds = (int) TimeSpentOnCourse::where('user_id', $buyerId)
                ->where('course_id', $webinar->id)
                ->sum('seconds_spent');
            $activity = self::formatActivity($seconds, $progress);

            $certs = Certificate::where('student_id', $buyerId)
                ->where('webinar_id', $webinar->id)
                ->count();

            $reviewUrl = $pendingAssignment
                ? route($reviewRoute, ['id' => $pendingAssignment->id])
                : $assignmentsFallback;

            $remindUrl = route($remindRoute, array_merge($remindParams, [
                'studentId' => $buyerId,
            ]));

            return [
                'id' => $buyerId,
                'name' => $buyer->full_name ?? 'طالب',
                'email' => $buyer->email ?? '',
                'avatar' => method_exists($buyer, 'getAvatar') ? $buyer->getAvatar() : null,
                'progress' => $progress,
                'progress_label' => number_format((float) $progress, 1) . '%',
                'activity' => $activity,
                'exams' => $passedExams,
                'assignments' => $assignmentTotal > 0
                    ? ($passedAssignments . '/' . $assignmentTotal)
                    : (string) $passedAssignments,
                'assignments_passed' => $passedAssignments,
                'certificates' => $certs,
                'joined_at' => !empty($sale->created_at) ? date('Y/m/d', (int) $sale->created_at) : '—',
                'review_url' => $reviewUrl,
                'remind_url' => $remindUrl,
                'has_pending' => !empty($pendingAssignment),
            ];
        });

        $progressFilter = (string) ($request?->get('progress', '') ?? '');
        if ($progressFilter === 'low') {
            $students = $students->filter(fn ($row) => (int) $row['progress'] < 40);
        } elseif ($progressFilter === 'mid') {
            $students = $students->filter(fn ($row) => (int) $row['progress'] >= 40 && (int) $row['progress'] < 80);
        } elseif ($progressFilter === 'high') {
            $students = $students->filter(fn ($row) => (int) $row['progress'] >= 80);
        }

        $students = $students->values();

        // Behind / completed computed from unfiltered enrolled set for KPI cards
        $allProgress = $sales->map(fn ($sale) => self::studentProgressPercent($webinar, $sale->buyer));
        $behindCount = $allProgress->filter(fn ($p) => (int) $p < 40)->count();
        $completedCount = $allProgress->filter(fn ($p) => (int) $p >= 100)->count();
        if ($completedCount === 0) {
            $completedCount = $allProgress->filter(fn ($p) => (int) $p >= 80)->count();
        }

        $avgProgress = $students->isNotEmpty()
            ? (int) round($students->avg('progress'))
            : (int) round((float) ($allProgress->avg() ?: 0));

        $salesCount = Sale::where('webinar_id', $webinar->id)->whereNull('refund_at')->count();

        return [
            'students' => $students->all(),
            'avg_progress' => $avgProgress,
            'pending_count' => $pendingCount,
            'behind_count' => $behindCount,
            'completed_count' => $completedCount,
            'first_pending_id' => $firstPendingId,
            'sales_count' => $salesCount,
            'assignment_total' => $assignmentTotal,
            'quiz_count' => $quizIds->count(),
            'certs_count' => Certificate::where('webinar_id', $webinar->id)->count(),
        ];
    }

    public static function studentProgressPercent($webinar, $student): int
    {
        if (empty($student)) {
            return 0;
        }

        $userId = (int) $student->id;

        try {
            $filesStat = $webinar->getFilesLearningProgressStat($userId);
            $sessionsStat = $webinar->getSessionsLearningProgressStat($userId);
            $textLessonsStat = $webinar->getTextLessonsLearningProgressStat($userId);
            $assignmentsStat = $webinar->getAssignmentsLearningProgressStat($userId);
            $quizzesStat = $webinar->getQuizzesLearningProgressStat($userId);
        } catch (\Throwable $e) {
            return 0;
        }

        $passed = ($filesStat['passed'] ?? 0)
            + ($sessionsStat['passed'] ?? 0)
            + ($textLessonsStat['passed'] ?? 0)
            + ($assignmentsStat['passed'] ?? 0)
            + ($quizzesStat['passed'] ?? 0);
        $count = ($filesStat['count'] ?? 0)
            + ($sessionsStat['count'] ?? 0)
            + ($textLessonsStat['count'] ?? 0)
            + ($assignmentsStat['count'] ?? 0)
            + ($quizzesStat['count'] ?? 0);

        if ($count < 1 || $passed < 1) {
            return 0;
        }

        return (int) max(0, min(100, round(($passed * 100) / $count)));
    }

    public static function formatActivity(int $seconds, int $progress): string
    {
        if ($seconds <= 0) {
            return $progress > 0 ? 'نشط' : 'لم يبدأ';
        }

        $minutes = intdiv($seconds, 60);
        $remain = $seconds % 60;

        return sprintf('%02d:%02d دقائق', $minutes, $remain);
    }

    public static function exportRows(Collection|array $students): Collection
    {
        return collect($students);
    }
}
