<?php

namespace App\Jobs;

use App\Models\AnalysisAiReport;
use App\Services\AnalyticsAiService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class GenerateAiAnalyticsReport implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    /**
     * Количество попыток выполнения задачи
     */
    public int $tries = 2;

    /**
     * Таймаут выполнения (5 минут для AI запросов)
     */
    public int $timeout = 300;

    /**
     * Задержка перед повторной попыткой (секунды)
     */
    public int $retryAfter = 10;

    /**
     * ID пользователя для которого генерируется отчет
     */
    public int $userId;

    /**
     * ID организации
     */
    public int $organizationId;

    /**
     * ID отдела
     */
    public int $departmentId;

    /**
     * Create a new job instance.
     */
    public function __construct(int $userId, int $organizationId, int $departmentId)
    {
        $this->userId = $userId;
        $this->organizationId = $organizationId;
        $this->departmentId = $departmentId;
    }

    /**
     * Execute the job.
     */
    public function handle(AnalyticsAiService $analyticsAiService): void
    {
        try {
            Log::info('Starting AI report generation', [
                'user_id' => $this->userId,
                'organization_id' => $this->organizationId,
                'department_id' => $this->departmentId,
            ]);

            // Временно устанавливаем пользователя для контекста
            auth()->loginUsingId($this->userId);

            // Генерируем отчет
            $report = $analyticsAiService->generateAiReport();

            Log::info('AI report generated successfully', [
                'report_id' => $report->id,
                'user_id' => $this->userId,
            ]);

            // TODO: Здесь можно добавить отправку уведомления пользователю
            // Notification::send($user, new ReportGeneratedNotification($report));

        } catch (\Exception $e) {
            Log::error('AI report generation failed', [
                'user_id' => $this->userId,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            // Пробрасываем исключение для повторной попытки
            throw $e;
        }
    }

    /**
     * Handle a job failure.
     */
    public function failed(\Throwable $exception): void
    {
        Log::error('AI report generation failed permanently', [
            'user_id' => $this->userId,
            'organization_id' => $this->organizationId,
            'error' => $exception->getMessage(),
        ]);

        // TODO: Уведомить пользователя об ошибке
        // Notification::send($user, new ReportGenerationFailedNotification());
    }
}
