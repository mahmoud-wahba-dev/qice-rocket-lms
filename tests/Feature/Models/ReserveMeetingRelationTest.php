<?php

namespace Tests\Feature\Models;

use App\Models\Meeting;
use App\Models\MeetingTime;
use App\Models\ReserveMeeting;
use App\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class ReserveMeetingRelationTest extends TestCase
{
    use DatabaseTransactions;

    public function test_meeting_relation_joins_through_meeting_times(): void
    {
        $sql = (new ReserveMeeting())->meeting()->toSql();

        $this->assertStringContainsString('meeting_times', $sql);
        $this->assertStringContainsString('meetings', $sql);
    }

    public function test_meeting_resolves_through_meeting_time(): void
    {
        $teacher = User::create([
            'full_name' => 'T', 'email' => 't_' . time() . rand(1000, 9999) . '@test.local',
            'role_name' => 'teacher', 'role_id' => 4, 'password' => 'x',
            'status' => 'active', 'created_at' => time(),
        ]);

        $meeting = Meeting::create(['creator_id' => $teacher->id, 'created_at' => time()]);
        $time = MeetingTime::create([
            'meeting_id' => $meeting->id, 'day_label' => 'saturday', 'time' => '10:00',
            'date' => time() + 3600, 'duration' => 60, 'status' => 'active',
            'created_at' => time(),
        ]);
        $reservation = ReserveMeeting::create([
            'meeting_time_id' => $time->id, 'user_id' => $teacher->id,
            'day' => '2026-01-01', 'date' => time() + 3600,
            'start_at' => time() + 3600, 'end_at' => time() + 7200,
            'paid_amount' => 0, 'status' => 'open', 'created_at' => time(),
        ]);

        $this->assertSame($meeting->id, $reservation->fresh()->meeting->id);
    }
}
