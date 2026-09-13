<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class StudentCalendarEvent extends Model
{
    protected $table = 'student_calendar_events';

    protected $guarded = ['id'];

    protected $casts = [
        'event_date' => 'date',
    ];

    public function user()
    {
        return $this->belongsTo(\App\User::class, 'user_id', 'id');
    }
}
