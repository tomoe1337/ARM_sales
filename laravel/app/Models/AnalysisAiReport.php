<?php

namespace App\Models;

use App\Models\Scopes\OrganizationScope;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AnalysisAiReport extends Model
{
    protected $fillable = [
        'user_id',
        'organization_id',
        'department_id',
        'report_type',
        'start_date',
        'end_date',
        'total_leads',
        'in_progress_count',
        'won_count',
        'lost_count',
        'employee_stats',
        'funnel_config',
        'revenue',
        'done_well',
        'done_bad',
        'general_result'
    ];

    protected $casts = [
        'employee_stats' => 'array',
        'funnel_config' => 'array',
        'start_date' => 'date',
        'end_date' => 'date',
        'revenue' => 'decimal:2',
    ];

    // Связи
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function organization(): BelongsTo
    {
        return $this->belongsTo(Organization::class);
    }

    public function department(): BelongsTo
    {
        return $this->belongsTo(Department::class);
    }

    // Global Scope для автоматической фильтрации
    protected static function booted()
    {
        static::addGlobalScope(new OrganizationScope);

        // Автоматическое заполнение organization_id и department_id при создании
        static::creating(function ($report) {
            if (auth()->check()) {
                $user = auth()->user();
                if (!$report->organization_id && $user->organization_id) {
                    $report->organization_id = $user->organization_id;
                }
                if (!$report->department_id && $user->department_id) {
                    $report->department_id = $user->department_id;
                }
            }
        });
    }
}
