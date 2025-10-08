<?php

namespace App\Services;

use App\Models\AnalysisAiReport;
use App\Models\Order;
use App\Models\Client;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Http;

class AnalyticsAiService
{
    /**
     * @return mixed
     */
    public function getWeeklyReportData($array_output = null): mixed
    {
        $orders = Order::join('users', 'orders.user_id', '=', 'users.id')
            ->where(function ($query) {
                $query->where('orders.created_at', '>=', Carbon::now()->subWeek())
                    ->orWhere('orders.updated_at', '>=', Carbon::now()->subWeek());
            })
            ->select(
                'orders.*',
                'users.full_name as employee_name'
            )
            ->get();
            
        if ($array_output) {
            // Получаем клиентов (лидов) за неделю
            $clients = $this->getClientsData();
            $total_leads = count($clients);
            $total_orders = $orders->count();
            // Поскольку все заказы BlueSales считаются оплаченными
            $successful_orders = $orders->count(); 
            $revenue = $orders->sum('total_amount');
            
            // Конверсия лидов в заказы
            $conversion_rate = $total_leads > 0 ? round(($total_orders / $total_leads) * 100, 1) : 0;

            $orders = [
                'week_start' => now()->startOfWeek()->format('d.m.Y'),
                'week_end' => now()->endOfWeek()->format('d.m.Y'),
                'total_leads' => $total_leads, // Общее количество лидов
                'total_orders' => $total_orders, // Количество заказов
                'successful_orders' => $successful_orders, // Успешные заказы (все заказы)
                'conversion_rate' => $conversion_rate, // Конверсия лидов в заказы
                'revenue' => $revenue,
            ];
        }

        return $orders;
    }

    public function generateAiReport()
    {
        $uri = 'https://api.proxyapi.ru/google/v1beta/models/gemini-2.0-flash-lite:generateContent';
        $orders = $this->getWeeklyReportData();
        $clients = $this->getClientsData();
        $total_revenue = $orders->sum('total_amount');
        
        $reportData = [
            'orders' => $orders->toArray(),
            'clients' => $clients
        ];
        
        $baseReport = json_encode($reportData);
        $payload = [
            "contents" => [
                [
                    "role" => "user",
                    "parts" => [
                        [
                            "text" => 'Ты — эксперт по аналитике продаж в компании которая работает с заказами из CRM BlueSales. На основе предоставленных данных о заказах и клиентах отдела продаж за эту неделю, проведи глубокий анализ и выдели:
Что сделано хорошо: перечисли 2–3 ключевых достижения команды, которые действительно способствовали росту выручки или повышению эффективности. Объясни, почему это важно и насколько это лучше средних показателей.
Что сделано плохо: выяви 2–3 скрытые проблемы или слабые места, которые напрямую связаны именно с работой отдела продаж и которые напрямую влияют на снижение выручки или увеличение времени закрытия сделок. Опираясь на данные, объясни, почему эти проблемы критичны.
Итоговый результат и рекомендации: дай общую оценку эффективности отдела по шкале от 1 до 10 и предложи 3 конкретных действия, которые можно внедрить в ближайшие 4–6 недель для улучшения выручки. Каждое действие должно быть:
основано на фактических данных
выполнимым без крупных изменений в структуре
с примерным ожидаемым эффектом (например, +15% к конверсии)
Не используй общие фразы вроде "нужно лучше работать с клиентами". Фокусируйся только на том, что неочевидно из простого просмотра CRM и может стать точкой роста при правильной корректировке.Добавить: "Помни, что данные поступают из CRM BlueSales, где часто в большенстве компаний каждый заказ = факт оплаты, 
а клиенты изначально являются лидами с разными статусами конверсии.Отвечай только на русском. Ответ дай в виде json с полями done_well (что сделано хорошо), done_bad(что сделано плохо), general_result(Итоговая оценка и рекомендации)']
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
        $orders = $orders->toArray();
        $employeeStats = $this->getEmployeeResults($orders);
        
        // Получаем клиентов для подсчета лидов
        $totalLeads = count($this->getClientsData());
        $totalOrders = count($orders);
        
        $AnalysisAiReport = AnalysisAiReport::create([
            'user_id' => auth()->user()->id,
            'report_type' => 'weekly',
            'start_date' => now()->startOfWeek(),
            'end_date' => now()->endOfWeek(),
            'employee_stats' => $employeeStats,
            'total_leads' => $totalLeads, // Общее количество лидов
            'in_progress_count' => $totalOrders, // Все заказы в работе
            'won_count' => $totalOrders, // Все заказы оплаченные
            'lost_count' => $totalLeads - $totalOrders, // Лиды без заказов
            'revenue' => $total_revenue,
            'done_well' => $content['done_well'],
            'done_bad' => $content['done_bad'],
            'general_result' => $content['general_result']
        ]);

        return $AnalysisAiReport;

    }

    private function getEmployeeResults(array $orders): array
    {
        // Получаем всех клиентов (лидов) за неделю
        $clients = $this->getClientsData();
        
        $stats = [];
        
        // Сначала считаем лидов по сотрудникам
        foreach ($clients as $client) {
            $employeeName = isset($client['user']) ? $client['user']['full_name'] : 'Неизвестный';
            
            if (!isset($stats[$employeeName])) {
                $stats[$employeeName] = [
                    'name' => $employeeName,
                    'total_leads' => 0, // Общее количество лидов
                    'orders' => 0, // Количество заказов
                    'revenue' => 0
                ];
            }
            
            $stats[$employeeName]['total_leads']++;
        }
        
        // Затем считаем заказы по сотрудникам
        foreach ($orders as $order) {
            $employeeName = $order['employee_name'];
            
            if (!isset($stats[$employeeName])) {
                $stats[$employeeName] = [
                    'name' => $employeeName,
                    'total_leads' => 0,
                    'orders' => 0,
                    'revenue' => 0
                ];
            }
            
            $stats[$employeeName]['orders']++;
            $stats[$employeeName]['revenue'] += (float)$order['total_amount'];
        }
        
        // Преобразуем в формат для отображения
        $employeeStats = array_values(array_map(function ($stat) {
            $conversionRate = $stat['total_leads'] > 0 
                ? round(($stat['orders'] / $stat['total_leads']) * 100, 1) 
                : 0;
                
            return [
                'name' => $stat['name'],
                'total' => $stat['total_leads'], // Общее количество лидов
                'won' => $stat['orders'], // Количество заказов
                'conversion_rate' => $conversionRate, // Конверсия лидов в заказы
                'revenue' => $stat['revenue']
            ];
        }, $stats));

        return $employeeStats;
    }

    public function getClientsData(): array
    {
        $clients = Client::where(function ($query) {
                $query->where('created_at', '>=', Carbon::now()->subWeek())
                    ->orWhere('updated_at', '>=', Carbon::now()->subWeek());
            })
            ->select([
                'full_name',
                'city', 
                'crm_status',
                'first_contact_date',
                'next_contact_date',
                'last_contact_date',
                'source',
                'sales_channel',
                'tags',
                'user_id',
                'created_at',
                'updated_at'
            ])
            ->with('user:id,full_name')
            ->get()
            ->toArray();
            
        return $clients;
    }
}
