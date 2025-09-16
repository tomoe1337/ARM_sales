<?php

namespace App\Services;

use App\Models\AnalysisAiReport;
use App\Models\Deal;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Http;

class AnalyticsAiService
{
    /**
     * @return mixed
     */
    public function getWeeklyReportData($array_output = null): mixed
    {
        $deals = Deal::join('users', 'deals.user_id', '=', 'users.id')
            ->where(function ($query) {
                $query->where('deals.created_at', '>=', Carbon::now()->subWeek())
                    ->orWhere('deals.closed_at', '>=', Carbon::now()->subWeek());
            })
            ->select(
                'deals.*',
                'users.full_name as employee_name'
            )
            ->get();
        if ($array_output) {
            $total_deals = $deals->count();
            $successful_deals = $deals->where('status', 'won')->count();
            $revenue = $deals->where('status', 'won')->sum('amount');

            $deals = [
                'week_start' => now()->startOfWeek()->format('d.m.Y'),
                'week_end' => now()->endOfWeek()->format('d.m.Y'),
                'total_deals' => $total_deals,
                'successful_deals' => $successful_deals,
                'conversion_rate' => $total_deals != 0 ? round(($successful_deals / $total_deals) * 100, 1) : "?",
                'revenue' => $revenue,
            ];
        }

        return $deals;
    }

    public function generateAiReport()
    {
        $uri = 'https://api.proxyapi.ru/google/v1beta/models/gemini-2.0-flash-lite:generateContent';
        $deals = $this->getWeeklyReportData();
        $total_revenue = $deals->sum('amount');
        $baseReport = $deals->toJson();
        $payload = [
            "contents" => [
                [
                    "role" => "user",
                    "parts" => [
                        [
                            "text" => 'Ты — эксперт по аналитике продаж в компании которая общение с клиентами ведет в чате мессенджера. На основе предоставленных данных о деятельности отдела продаж за эту неделю, проведи глубокий анализ и выдели:
Что сделано хорошо: перечисли 2–3 ключевых достижения команды, которые действительно способствовали росту выручки или повышению эффективности. Объясни, почему это важно и насколько это лучше средних показателей.
Что сделано плохо: выяви 2–3 скрытые проблемы или слабые места, которые напрямую влияют на снижение выручки или увеличение времени закрытия сделок. Опираясь на данные, объясни, почему эти проблемы критичны.
Итоговый результат и рекомендации: дай общую оценку эффективности отдела по шкале от 1 до 10 и предложи 3 конкретных действия, которые можно внедрить в ближайшие 4–6 недель для улучшения выручки. Каждое действие должно быть:
основано на фактических данных
выполнимым без крупных изменений в структуре
с примерным ожидаемым эффектом (например, +15% к конверсии)
Не используй общие фразы вроде "нужно лучше работать с клиентами". Фокусируйся только на том, что неочевидно из простого просмотра CRM и может стать точкой роста при правильной корректировке. Отвечай только на русском. Ответ дай в виде json с полями done_well (что сделано хорошо), done_bad(что сделано плохо), general_result(Итоговая оценка и рекомендации)']
                    ]
                ],
                [
                    "role" => "user",
                    "parts" => [
                        [
                            "text" => "Вот отчет, проанализируй его: " . $baseReport
                        ]
                    ]
                ]
            ],
            "generationConfig" => [
                "responseMimeType" => "application/json",
                "responseSchema" => [
                    "type" => "object",
                    "properties" => [
                        "done_well" => [
                            "type" => "string"
                        ],
                        "done_bad" => [
                            "type" => "string"
                        ],
                        "general_result" => [
                            "type" => "string"
                        ]
                    ],
                    "required" => [
                        "done_well",
                        "done_bad",
                        "general_result"
                    ]
                ]
            ]
        ];
        $proxyapi_token = env('AI_API_TOKEN');
        $content = json_decode(file_get_contents(__DIR__ . '/generate.json'), true);

        $real = true; //todo:убрать мок
        if ($real) {
            $response = Http::withOptions(['verify' => false])
                ->withHeaders([
                    'Authorization' => "Bearer {$proxyapi_token}",
                    'Content-Type' => 'application/json',])
                ->post($uri, $payload);

            $content = $response->json()['candidates'][0]['content']['parts'][0]['text'] ?? null;
            $content = json_decode($content, true);
        }
        $deals = $deals->toArray();
        $employeeStats = $this->getEmployeeResults($deals);
        $AnalysisAiReport = AnalysisAiReport::create([
            'user_id' => auth()->user()->id,
            'report_type' => 'weekly',
            'start_date' => now()->startOfWeek(),
            'end_date' => now()->endOfWeek(),
            'employee_stats' => $employeeStats,
            'total_leads' => count($deals),
            'in_progress_count' => count(array_filter($deals, fn($d) => $d['status'] === 'in_progress')),
            'won_count' => count(array_filter($deals, fn($d) => $d['status'] === 'won')),
            'lost_count' => count(array_filter($deals, fn($d) => $d['status'] === 'lost')),
            'revenue' => $total_revenue,
            'done_well' => $content['done_well'],
            'done_bad' => $content['done_bad'],
            'general_result' => $content['general_result']
        ]);

        return $AnalysisAiReport;

    }

    private function getEmployeeResults(array $deals): array
    {
        $stats = [];
        foreach ($deals as $deal) {
            $name = $deal['employee_name'];

            if (!isset($stats[$name])) {
                $stats[$name] = [
                    'name' => $name,
                    'total' => 0,
                    'won' => 0,
                    'in_progress' => 0,
                    'lost' => 0,
                    'revenue' => 0
                ];
            }

            $stats[$name]['total']++;
            switch ($deal['status']) {
                case 'won':
                    $stats[$name]['won']++;
                    $stats[$name]['revenue'] += (float)$deal['amount'];
                    break;
                case 'in_progress':
                    $stats[$name]['in_progress']++;
                    break;
                case 'lost':
                    $stats[$name]['lost']++;
                    break;

            }
        }
        $employeeStats = array_values(array_map(function ($stat) {
            $stat['conversion_rate'] = $stat['total'] > 0 ? round(($stat['won'] / $stat['total']) * 100, 1) : 0;
            return $stat;
        }, $stats));

        return $employeeStats;
    }
}
