<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AnalysisAiReport extends Model
{
    protected $fillable = [
        'user_id',
        'report_type',
        'start_date',
        'end_date',
        'total_leads',
        'in_progress_count',
        'won_count',
        'lost_count',
        'employee_stats',
        'revenue',
        'done_well',
        'done_bad',
        'general_result'
    ];

    protected $casts = [
        'employee_stats' => 'array'
    ];
}
