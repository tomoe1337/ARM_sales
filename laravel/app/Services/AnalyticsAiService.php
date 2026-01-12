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
        $weekStart = now()->startOfWeek();
        $weekEnd = now()->endOfWeek();
        $departmentId = auth()->user()->department_id;
        $organizationId = auth()->user()->organization_id;
        
        // Получаем данные один раз для всех операций
        $allClients = Client::withoutGlobalScopes()
            ->where('organization_id', $organizationId)
            ->where('department_id', $departmentId)
            ->whereBetween('created_at', [$weekStart, $weekEnd])
            ->get();
        
        $allOrders = Order::withoutGlobalScopes()
            ->where('organization_id', $organizationId)
            ->where('department_id', $departmentId)
            ->whereBetween('created_at', [$weekStart, $weekEnd])
            ->with('user:id,full_name')
            ->get();
        
        $data = $this->prepareDataForAI($weekStart, $weekEnd, $allClients, $allOrders);
        $content = $this->callAiApi($data);
        
        return $this->saveReport($content, $data, $weekStart, $weekEnd, $allClients, $allOrders, $departmentId, $organizationId);
    }

    private function prepareDataForAI(Carbon $weekStart, Carbon $weekEnd, $allClients, $allOrders): array
    {
        return [
            'current' => $this->getCurrentMetrics($weekStart, $weekEnd, $allClients, $allOrders),
            'comparison' => $this->getComparison($weekStart, $weekEnd),
            'segments' => $this->getSegments($weekStart, $weekEnd),
            'employees' => $this->getEmployeePerformance($weekStart, $weekEnd),
            'anomalies' => $this->getAnomalies(),
            'correlations' => $this->getCorrelations($weekStart, $weekEnd),
        ];
    }

    private function getCurrentMetrics(Carbon $start, Carbon $end, $allClients = null, $allOrders = null): array
    {
        if ($allClients === null) {
            $allClients = Client::whereBetween('created_at', [$start, $end])->get();
        }
        if ($allOrders === null) {
            $allOrders = Order::whereBetween('created_at', [$start, $end])->get();
        }
        
        $leads = $allClients->count();
        $ordersCount = $allOrders->count();
        
        return [
            'leads' => $leads,
            'orders' => $ordersCount,
            'revenue' => (float) $allOrders->sum('total_amount'),
            'conversion' => $leads > 0 ? round(($ordersCount / $leads) * 100, 1) : 0,
            'avg_order' => $ordersCount > 0 ? (float) $allOrders->avg('total_amount') : 0,
        ];
    }

    private function getComparison(Carbon $start, Carbon $end): array
    {
        $prevStart = $start->copy()->subWeek();
        $prevEnd = $end->copy()->subWeek();
        
        $prevLeads = Client::whereBetween('created_at', [$prevStart, $prevEnd])->count();
        $prevOrders = Order::whereBetween('created_at', [$prevStart, $prevEnd])->get();
        $prevRevenue = (float) $prevOrders->sum('total_amount');
        
        $current = $this->getCurrentMetrics($start, $end);
        
        return [
            'previous_week' => [
                'leads' => $prevLeads,
                'orders' => $prevOrders->count(),
                'revenue' => $prevRevenue,
                'conversion' => $prevLeads > 0 ? round(($prevOrders->count() / $prevLeads) * 100, 1) : 0,
            ],
            'changes' => [
                'leads' => $this->calculateChange($prevLeads, $current['leads']),
                'revenue' => $this->calculateChange($prevRevenue, $current['revenue']),
                'conversion' => $this->calculateChange(
                    $prevLeads > 0 ? round(($prevOrders->count() / $prevLeads) * 100, 1) : 0,
                    $current['conversion']
                ),
            ],
        ];
    }

    private function getSegments(Carbon $start, Carbon $end): array
    {
        return [
            'by_source' => $this->segmentBy('source', $start, $end),
            'by_crm_status' => $this->segmentBy('crm_status', $start, $end),
            'by_city' => $this->segmentBy('city', $start, $end),
        ];
    }

    private function segmentBy(string $field, Carbon $start, Carbon $end): array
    {
        $leads = Client::whereBetween('created_at', [$start, $end])
            ->whereNotNull($field)
            ->selectRaw("{$field}, COUNT(*) as count")
            ->groupBy($field)
            ->get()
            ->keyBy($field);

        $orders = Order::whereBetween('created_at', [$start, $end])
            ->whereHas('client', fn($q) => $q->whereNotNull($field))
            ->with('client:id,' . $field)
            ->get()
            ->groupBy("client.{$field}");

        return $leads->map(function ($leadGroup, $value) use ($orders, $field) {
            $orderGroup = $orders->get($value, collect());
            $ordersCount = $orderGroup->count();
            $revenue = (float) $orderGroup->sum('total_amount');
            
            return [
                $field => $value,
                'leads' => $leadGroup->count,
                'orders' => $ordersCount,
                'conversion' => $leadGroup->count > 0 
                    ? round(($ordersCount / $leadGroup->count) * 100, 1) 
                    : 0,
                'avg_order' => $ordersCount > 0 ? round($revenue / $ordersCount, 0) : 0,
            ];
        })->values()->sortByDesc('conversion')->take(5)->toArray();
    }

    private function getEmployeePerformance(Carbon $start, Carbon $end): array
    {
        $leads = Client::whereBetween('created_at', [$start, $end])
            ->selectRaw('user_id, COUNT(*) as count')
            ->groupBy('user_id')
            ->with('user:id,full_name')
            ->get()
            ->keyBy('user_id');

        $orders = Order::whereBetween('created_at', [$start, $end])
            ->selectRaw('user_id, COUNT(*) as count, SUM(total_amount) as revenue')
            ->groupBy('user_id')
            ->with('user:id,full_name')
            ->get()
            ->keyBy('user_id');

        $performance = $leads->map(function ($leadGroup) use ($orders) {
            $orderGroup = $orders->get($leadGroup->user_id);
            $ordersCount = $orderGroup?->count ?? 0;
            $revenue = (float) ($orderGroup?->revenue ?? 0);
            
            return [
                'name' => $leadGroup->user->full_name ?? 'Неизвестный',
                'leads' => $leadGroup->count,
                'orders' => $ordersCount,
                'conversion' => $leadGroup->count > 0 
                    ? round(($ordersCount / $leadGroup->count) * 100, 1) 
                    : 0,
                'revenue' => $revenue,
            ];
        })->sortByDesc('conversion')->values();

        return [
            'top_2' => $performance->take(2)->toArray(),
            'worst_1' => $performance->take(-1)->first(),
            'avg_conversion' => round($performance->avg('conversion'), 1),
        ];
    }

    private function getAnomalies(): array
    {
        $stuckDeals = \App\Models\Deal::where('status', '!=', 'won')
            ->where('status', '!=', 'lost')
            ->where('created_at', '<', now()->subDays(20))
            ->with('client:id,name')
            ->get()
            ->take(5);

        return [
            'stuck_deals' => [
                'count' => $stuckDeals->count(),
                'total_value' => (float) $stuckDeals->sum('amount'),
                'avg_days_stuck' => $stuckDeals->avg(fn($d) => $d->created_at->diffInDays(now())),
            ],
        ];
    }

    private function getCorrelations(Carbon $start, Carbon $end): array
    {
        $segments = $this->getSegments($start, $end);
        
        $sources = collect($segments['by_source'] ?? []);
        $bestSource = $sources->sortByDesc('conversion')->first();
        $worstSource = $sources->sortBy('conversion')->first();

        $getValue = fn($item) => $item ? collect($item)->except(['leads', 'orders', 'conversion', 'avg_order'])->first() : null;

        return [
            'source_vs_conversion' => [
                'best' => $bestSource ? ($getValue($bestSource) . " ({$bestSource['conversion']}%)") : null,
                'worst' => $worstSource ? ($getValue($worstSource) . " ({$worstSource['conversion']}%)") : null,
                'gap' => $bestSource && $worstSource 
                    ? round($bestSource['conversion'] - $worstSource['conversion'], 1) 
                    : 0,
            ],
        ];
    }

    private function callAiApi(array $data): ?array
    {
        $token = config('services.ai.token') ?? env('AI_API_TOKEN');
        if (!$token) {
            return null;
        }

        try {
            $response = Http::timeout(60)
                ->withHeaders([
                    'Authorization' => "Bearer {$token}",
                    'Content-Type' => 'application/json',
                ])
                ->post(config('services.ai.uri', 'https://api.proxyapi.ru/google/v1beta/models/gemini-2.0-flash-lite:generateContent'), [
                    'contents' => [[
                        'role' => 'user',
                        'parts' => [[
                            'text' => $this->buildPrompt($data),
                        ]],
                    ]],
                    'generationConfig' => [
                        'responseMimeType' => 'application/json',
                        'responseSchema' => [
                            'type' => 'object',
                            'properties' => [
                                'done_well' => ['type' => 'string'],
                                'done_bad' => ['type' => 'string'],
                                'general_result' => ['type' => 'string'],
                            ],
                            'required' => ['done_well', 'done_bad', 'general_result'],
                        ],
                    ],
                ]);

            if ($response->successful()) {
                $responseData = $response->json();
                $text = $responseData['candidates'][0]['content']['parts'][0]['text'] ?? null;
                
                return $text ? json_decode($text, true) : null;
            }
        } catch (\Exception $e) {
            \Log::error('AI API error', ['message' => $e->getMessage()]);
        }

        return null;
    }

    private function buildPrompt(array $data): string
    {
        $prompt = "Ты — эксперт по аналитике продаж в компании которая работает с заказами из CRM BlueSales. ";
        $prompt .= "На основе предоставленных данных о заказах и клиентах отдела продаж за эту неделю, проведи глубокий анализ и выдели:\n\n";
        
        // Инструкция по преобразованию технических названий в человекочитаемые
        $prompt .= "КРИТИЧЕСКИ ВАЖНО - Преобразование названий: В ответе НИКОГДА не используй технические названия (snake_case, английские слова, сокращения). ";
        $prompt .= "ВСЕГДА преобразуй их в понятные русские названия. Это обязательное требование!\n\n";
        $prompt .= "Примеры преобразования источников: 'cold_call' → 'Холодные звонки', 'referral' → 'Рекомендации', ";
        $prompt .= "'exhibition' → 'Выставки', 'partner' → 'Партнеры', 'website' → 'Сайт', 'social_media' → 'Социальные сети'.\n\n";
        $prompt .= "Примеры преобразования статусов: 'closed_won' → 'Закрытые успешно' или 'Выигранные сделки', ";
        $prompt .= "'closed_lost' → 'Закрытые неудачно' или 'Проигранные сделки', ";
        $prompt .= "'new' → 'Новые', 'in_progress' → 'В работе', 'qualified' → 'Квалифицированные', ";
        $prompt .= "'proposal' → 'Коммерческое предложение', 'negotiation' → 'Переговоры'.\n\n";
        $prompt .= "Для городов используй полные названия (например, 'Ростов-на-Дону' вместо 'rostov').\n\n";
        $prompt .= "ПРАВИЛО: Если видишь любое техническое название (английские слова, подчеркивания, сокращения) - ";
        $prompt .= "ОБЯЗАТЕЛЬНО преобразуй его в понятное русское название. Если название уже на русском и понятное - оставляй как есть.\n\n";
        
        // Запрет markdown
        $prompt .= "ВАЖНО - Форматирование: Не используй markdown разметку (**, ##, списки с - и т.д.). ";
        $prompt .= "Пиши обычным текстом с переносами строк. Используй только простые абзацы. ";
        $prompt .= "Для выделения важного используй обычный текст, не форматирование.\n\n";
        
        $prompt .= "Что сделано хорошо: перечисли 2–3 ключевых достижения команды, которые действительно способствовали росту выручки или повышению эффективности. ";
        $prompt .= "Объясни, почему это важно и насколько это лучше средних показателей.\n\n";
        $prompt .= "Что сделано плохо: выяви 2–3 скрытые проблемы или слабые места, которые напрямую связаны именно с работой отдела продаж и которые напрямую влияют на снижение выручки или увеличение времени закрытия сделок. ";
        $prompt .= "Опираясь на данные, объясни, почему эти проблемы критичны. Особое внимание удели тому как клиенты проходят по воронке, насколько долго идут.\n\n";
        $prompt .= "Итоговый результат и рекомендации: дай общую оценку эффективности отдела по шкале от 1 до 10 и предложи 3 конкретных действия, которые можно внедрить в ближайшие 4–6 недель для улучшения выручки. ";
        $prompt .= "Каждое действие должно быть основано на фактических данных, выполнимым без крупных изменений в структуре, с примерным ожидаемым эффектом (например, +15% к конверсии).\n\n";
        $prompt .= "Не используй общие фразы вроде 'нужно лучше работать с клиентами'. Фокусируйся только на том, что неочевидно из простого просмотра CRM и может стать точкой роста при правильной корректировке.\n\n";
        $prompt .= "Помни, что данные поступают из CRM BlueSales, где часто в большинстве компаний каждый заказ = факт оплаты, ";
        $prompt .= "а клиенты изначально являются лидами с разными статусами конверсии.\n\n";
        $prompt .= "Отвечай только на русском. Ответ дай в виде json с полями done_well (что сделано хорошо), done_bad (что сделано плохо), general_result (Итоговая оценка и рекомендации).\n\n";
        $prompt .= "Данные для анализа:\n" . json_encode($data, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
        
        return $prompt;
    }

    private function saveReport(?array $content, array $data, Carbon $weekStart, Carbon $weekEnd, $allClients, $allOrders, ?int $departmentId, ?int $organizationId): AnalysisAiReport
    {
        $content = $content ?? $this->getDefaultContent();
        $current = $data['current'];
        
        $orders = $allOrders->map(function ($order) {
            return [
                'id' => $order->id,
                'user_id' => $order->user_id,
                'total_amount' => (float) $order->total_amount,
                'employee_name' => $order->user->full_name ?? 'Неизвестный',
            ];
        })->toArray();
        $employeeStats = $this->getEmployeeResults($orders);
        
        // Получаем конфигурацию воронки от AI, используя уже полученные данные
        $funnelConfig = $this->getFunnelConfigFromAI(
            $weekStart, 
            $weekEnd,
            $departmentId,
            $organizationId,
            $allClients,
            $allOrders
        );
        
        return AnalysisAiReport::create([
            'user_id' => auth()->id(),
            'organization_id' => auth()->user()->organization_id,
            'department_id' => auth()->user()->department_id,
            'report_type' => 'weekly',
            'start_date' => $weekStart,
            'end_date' => $weekEnd,
            'total_leads' => $current['leads'],
            'won_count' => $current['orders'],
            'revenue' => $current['revenue'],
            'employee_stats' => $employeeStats,
            'funnel_config' => $funnelConfig,
            'done_well' => $content['done_well'],
            'done_bad' => $content['done_bad'],
            'general_result' => $content['general_result'],
        ]);
    }

    private function getDefaultContent(): array
    {
        return [
            'done_well' => 'Данные отчета недоступны. Проверьте настройки AI_API_TOKEN.',
            'done_bad' => 'Невозможно провести анализ без данных от AI API.',
            'general_result' => 'Настройте AI_API_TOKEN для использования автоматической генерации отчетов.',
        ];
    }

    /**
     * Получает конфигурацию воронки от AI
     */
    private function getFunnelConfigFromAI(Carbon $start, Carbon $end, ?int $departmentId, ?int $organizationId, $allClients = null, $allOrders = null): ?array
    {
        try {
            // Получаем данные о статусах клиентов, используя уже полученные данные
            $funnelData = $this->getFunnelDataForAI($start, $end, $departmentId, $organizationId, $allClients, $allOrders);
            
            if (empty($funnelData['statuses'])) {
                \Log::info('No statuses found for funnel config', ['start' => $start, 'end' => $end]);
                return null;
            }
            
            // Запрашиваем AI для категоризации
            $config = $this->callAiForFunnelConfig($funnelData);
            
            if (!$config) {
                \Log::warning('AI did not return funnel config', ['funnelData' => $funnelData]);
            }
            
            return $config;
        } catch (\Exception $e) {
            \Log::error('Failed to get funnel config from AI', ['error' => $e->getMessage(), 'trace' => $e->getTraceAsString()]);
            return null;
        }
    }
    
    /**
     * Получает данные о статусах для передачи в AI
     */
    private function getFunnelDataForAI(Carbon $start, Carbon $end, ?int $departmentId, ?int $organizationId, $allClients = null, $allOrders = null): array
    {
        // Используем уже полученные данные, если они переданы
        if ($allClients === null) {
            $allClients = Client::withoutGlobalScopes()
                ->where('organization_id', $organizationId)
                ->where('department_id', $departmentId)
                ->whereBetween('created_at', [$start, $end])
                ->get();
        }
        
        $totalLeads = $allClients->count();
        
        // Используем уже полученные заказы, если они переданы
        if ($allOrders === null) {
            $clientsWithOrdersIds = Order::withoutGlobalScopes()
                ->where('organization_id', $organizationId)
                ->where('department_id', $departmentId)
                ->whereBetween('created_at', [$start, $end])
                ->pluck('client_id')
                ->unique()
                ->toArray();
        } else {
            $clientsWithOrdersIds = $allOrders->pluck('client_id')->unique()->toArray();
        }
        
        $statusStats = $allClients
            ->filter(fn($client) => !empty($client->crm_status))
            ->groupBy('crm_status')
            ->map(function ($clients, $status) use ($clientsWithOrdersIds, $totalLeads) {
                $total = $clients->count();
                $withOrders = $clients->filter(fn($c) => in_array($c->id, $clientsWithOrdersIds))->count();
                $orderRate = $total > 0 ? ($withOrders / $total) * 100 : 0;
                $conversionFromLeads = $totalLeads > 0 ? ($total / $totalLeads) * 100 : 0;
                
                return [
                    'status' => $status,
                    'count' => $total,
                    'with_orders' => $withOrders,
                    'order_rate' => round($orderRate, 1),
                    'conversion_from_leads' => round($conversionFromLeads, 1),
                ];
            })
            ->sortByDesc('count')
            ->values();
        
        return [
            'total_leads' => $totalLeads,
            'total_orders' => count($clientsWithOrdersIds),
            'statuses' => $statusStats->toArray(),
        ];
    }
    
    /**
     * Запрашивает AI для категоризации воронки
     */
    private function callAiForFunnelConfig(array $funnelData): ?array
    {
        $token = config('services.ai.token') ?? env('AI_API_TOKEN');
        if (!$token) {
            return null;
        }
        
        $prompt = $this->buildFunnelConfigPrompt($funnelData);
        
        try {
            $response = Http::timeout(30)
                ->withHeaders([
                    'Authorization' => "Bearer {$token}",
                    'Content-Type' => 'application/json',
                ])
                ->post(config('services.ai.uri', 'https://api.proxyapi.ru/google/v1beta/models/gemini-2.0-flash-lite:generateContent'), [
                    'contents' => [[
                        'role' => 'user',
                        'parts' => [[
                            'text' => $prompt,
                        ]],
                    ]],
                ]);
            
            if ($response->successful()) {
                $content = $response->json();
                $text = $content['candidates'][0]['content']['parts'][0]['text'] ?? null;
                
                if ($text) {
                    // Пытаемся извлечь JSON из ответа
                    $jsonMatch = [];
                    if (preg_match('/\{[\s\S]*\}/', $text, $jsonMatch)) {
                        $config = json_decode($jsonMatch[0], true);
                        if ($config && isset($config['stages'])) {
                            return $config;
                        }
                    }
                }
            }
        } catch (\Exception $e) {
            \Log::error('AI API error for funnel config', ['message' => $e->getMessage()]);
        }
        
        return null;
    }
    
    /**
     * Строит промпт для AI категоризации воронки
     */
    private function buildFunnelConfigPrompt(array $funnelData): string
    {
        $prompt = "Ты эксперт по аналитике продаж. Проанализируй статусы клиентов и создай оптимальную конфигурацию воронки продаж.\n\n";
        $prompt .= "Данные о статусах:\n";
        $prompt .= "- Всего лидов: {$funnelData['total_leads']}\n";
        $prompt .= "- Всего заказов: {$funnelData['total_orders']}\n\n";
        $prompt .= "Статусы клиентов:\n";
        
        foreach ($funnelData['statuses'] as $stat) {
            $prompt .= "- {$stat['status']}: {$stat['count']} клиентов, ";
            $prompt .= "{$stat['with_orders']} с заказами ({$stat['order_rate']}%), ";
            $prompt .= "конверсия от лидов: {$stat['conversion_from_leads']}%\n";
        }
        
        $prompt .= "\nЗадачи:\n";
        $prompt .= "1. Определи, какие статусы относятся к воронке продаж (до закрытия сделки)\n";
        $prompt .= "2. Определи, какие статусы относятся к выполнению заказа (после закрытия - доставка, завершение и т.д.)\n";
        $prompt .= "3. Сгруппируй похожие статусы (например, 'closed_won', 'delivered', 'completed' можно сгруппировать)\n";
        $prompt .= "4. Переведи все названия на русский в человекочитаемый вид\n";
        $prompt .= "5. Исключи статусы, которые не относятся к воронке продаж\n\n";
        
        $prompt .= "Верни JSON в следующем формате:\n";
        $prompt .= "{\n";
        $prompt .= "  \"stages\": [\n";
        $prompt .= "    {\"name\": \"Человекочитаемое название\", \"original\": [\"статус1\", \"статус2\"], \"type\": \"sales\"},\n";
        $prompt .= "    {\"name\": \"Завершенные сделки\", \"original\": [\"closed_won\", \"delivered\"], \"type\": \"final\"}\n";
        $prompt .= "  ],\n";
        $prompt .= "  \"excluded\": [\"статус1\", \"статус2\"],\n";
        $prompt .= "  \"humanized\": {\"статус1\": \"Человекочитаемое название\", \"статус2\": \"Другое название\"}\n";
        $prompt .= "}\n\n";
        $prompt .= "Где:\n";
        $prompt .= "- stages: этапы воронки в правильном порядке (от ранних к поздним)\n";
        $prompt .= "- type: 'sales' для этапов продаж, 'final' для финальных этапов\n";
        $prompt .= "- excluded: статусы, которые исключить из воронки\n";
        $prompt .= "- humanized: маппинг оригинальных названий на человекочитаемые\n";
        $prompt .= "\nОтвечай только JSON, без дополнительного текста.";
        
        return $prompt;
    }

    private function calculateChange(float $previous, float $current): float
    {
        if ($previous == 0) {
            return $current > 0 ? 100 : 0;
        }
        
        return round((($current - $previous) / $previous) * 100, 1);
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
