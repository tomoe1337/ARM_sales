<?php

namespace App\Http\Controllers\AnalyticsAI\Ai;

use App\Http\Controllers\Controller;
use App\Models\AnalysisAiReport;
use App\Models\Order;
use App\Models\Client;
use App\Models\User;
use App\Services\AnalyticsAiService;
use Illuminate\Support\Carbon;

class ShowController extends Controller
{
    public function __construct(
        protected AnalyticsAiService $analyticsAiService
    ) {}

    public function __invoke(AnalysisAiReport $analysisAiReport)
    {
        $weekStart = $analysisAiReport->start_date;
        $weekEnd = $analysisAiReport->end_date;
        $prevWeekStart = $weekStart->copy()->subWeek();
        $prevWeekEnd = $weekEnd->copy()->subWeek();

        // Текущая неделя
        $currentLeads = $analysisAiReport->total_leads;
        $currentOrders = $analysisAiReport->won_count;
        $currentRevenue = $analysisAiReport->revenue;
        $currentConversion = $currentLeads > 0 
            ? round(($currentOrders / $currentLeads) * 100, 1) 
            : 0;

        // Прошлая неделя
        $prevReport = AnalysisAiReport::where('department_id', $analysisAiReport->department_id)
            ->where('start_date', $prevWeekStart)
            ->first();
        
        $comparison = $prevReport ? [
            'leads' => $this->calculateChange((float) $prevReport->total_leads, (float) $currentLeads),
            'orders' => $this->calculateChange((float) $prevReport->won_count, (float) $currentOrders),
            'revenue' => $this->calculateChange((float) $prevReport->revenue, (float) $currentRevenue),
            'conversion' => $this->calculateChange(
                $prevReport->total_leads > 0 
                    ? round(($prevReport->won_count / $prevReport->total_leads) * 100, 1) 
                    : 0.0,
                $currentConversion
            ),
        ] : null;

        // Сегментация по источникам
        $segments = $this->getSegments(
            $weekStart, 
            $weekEnd,
            $analysisAiReport->department_id,
            $analysisAiReport->organization_id
        );

        // Воронка с конверсией
        $funnel = $this->buildFunnel($analysisAiReport, $weekStart, $weekEnd, false); // false = краткая версия
        $fullFunnel = $this->buildFunnel($analysisAiReport, $weekStart, $weekEnd, true); // true = полная версия

        // Сотрудники с выручкой
        $employeeStats = collect($analysisAiReport->employee_stats ?? [])
            ->map(function ($stat) {
                $name = $stat['name'] ?? 'Неизвестный';
                
                // Если имя - это число, пытаемся получить полное имя из БД
                if (is_numeric($name) && $name > 0) {
                    $user = User::find($name);
                    if ($user && $user->full_name) {
                        $name = $user->full_name;
                    } elseif ($user) {
                        $name = $user->name ?? "Сотрудник #{$name}";
                    } else {
                        $name = "Сотрудник #{$name}";
                    }
                }
                
                return [
                    'name' => $name,
                    'leads' => $stat['total'] ?? 0,
                    'orders' => $stat['won'] ?? 0,
                    'conversion' => $stat['conversion_rate'] ?? 0,
                    'revenue' => $stat['revenue'] ?? 0,
                ];
            })
            ->sortByDesc('conversion')
            ->values();

        return view('analyticsAI.report', compact(
            'analysisAiReport',
            'comparison',
            'segments',
            'funnel',
            'fullFunnel',
            'employeeStats'
        ));
    }

    private function getSegments(Carbon $start, Carbon $end, ?int $departmentId = null, ?int $organizationId = null): array
    {
        // Если не переданы, используем из текущего контекста (для обратной совместимости)
        if (!$departmentId || !$organizationId) {
            $user = auth()->user();
            $departmentId = $departmentId ?? $user->department_id;
            $organizationId = $organizationId ?? $user->organization_id;
        }
        
        $leads = Client::withoutGlobalScopes()
            ->where('organization_id', $organizationId)
            ->where('department_id', $departmentId)
            ->whereBetween('created_at', [$start, $end])
            ->whereNotNull('source')
            ->selectRaw('source, COUNT(*) as count')
            ->groupBy('source')
            ->get()
            ->keyBy('source');

        $orders = Order::withoutGlobalScopes()
            ->where('organization_id', $organizationId)
            ->where('department_id', $departmentId)
            ->whereBetween('created_at', [$start, $end])
            ->whereHas('client', fn($q) => $q->whereNotNull('source'))
            ->with('client:id,source')
            ->get()
            ->groupBy('client.source');

        return $leads->map(function ($leadGroup, $source) use ($orders) {
            $orderGroup = $orders->get($source, collect());
            $ordersCount = $orderGroup->count();
            $revenue = (float) $orderGroup->sum('total_amount');
            
            return [
                'source' => $this->humanizeSource($source),
                'source_raw' => $source, // Сохраняем оригинальное значение для сортировки
                'leads' => $leadGroup->count,
                'orders' => $ordersCount,
                'conversion' => $leadGroup->count > 0 
                    ? round(($ordersCount / $leadGroup->count) * 100, 1) 
                    : 0,
                'revenue' => $revenue,
            ];
        })->sortByDesc('conversion')->values()->take(5)->toArray();
    }

    private function buildFunnel(AnalysisAiReport $report, Carbon $start, Carbon $end, bool $fullVersion = false): array
    {
        // Получаем department_id и organization_id из отчета, а не текущего пользователя
        $departmentId = $report->department_id;
        $organizationId = $report->organization_id;
        
        // Получаем всех клиентов за период для конкретного департамента
        $allClients = Client::withoutGlobalScopes()
            ->where('organization_id', $organizationId)
            ->where('department_id', $departmentId)
            ->whereBetween('created_at', [$start, $end])
            ->get();
        
        $totalLeads = $allClients->count();
        
        // Получаем ID клиентов с заказами для этого департамента
        $clientsWithOrdersIds = Order::withoutGlobalScopes()
            ->where('organization_id', $organizationId)
            ->where('department_id', $departmentId)
            ->whereBetween('created_at', [$start, $end])
            ->pluck('client_id')
            ->unique()
            ->toArray();
        
        // Получаем все уникальные статусы и считаем статистику
        $statusStats = $allClients
            ->filter(fn($client) => !empty($client->crm_status))
            ->groupBy('crm_status')
            ->map(function ($clients, $status) use ($clientsWithOrdersIds) {
                $total = $clients->count();
                $withOrders = $clients->filter(fn($c) => in_array($c->id, $clientsWithOrdersIds))->count();
                $orderRate = $total > 0 ? ($withOrders / $total) * 100 : 0;
                
                return [
                    'status' => $status,
                    'count' => $total,
                    'with_orders' => $withOrders,
                    'order_rate' => $orderRate,
                ];
            })
            ->sortByDesc('count')
            ->values();
        
        // Используем конфигурацию от AI, если есть и не нужна полная версия
        $funnelConfig = $report->funnel_config;
        if ($funnelConfig && !$fullVersion && isset($funnelConfig['stages'])) {
            return $this->buildFunnelFromConfig($allClients, $statusStats, $totalLeads, $clientsWithOrdersIds, $funnelConfig);
        }
        
        // Иначе строим воронку по старой логике (полная версия)
        return $this->buildFunnelFull($allClients, $statusStats, $totalLeads, $clientsWithOrdersIds);
    }
    
    /**
     * Строит воронку на основе AI-конфигурации (краткая версия)
     */
    private function buildFunnelFromConfig($allClients, $statusStats, int $totalLeads, array $clientsWithOrdersIds, array $config): array
    {
        $funnel = [];
        $humanized = $config['humanized'] ?? [];
        $excluded = $config['excluded'] ?? [];
        
        // Этап 1: Все лиды
        $funnel[] = [
            'name' => 'Все лиды',
            'count' => $totalLeads,
            'conversion' => null,
        ];
        
        // Строим этапы по конфигурации AI
        foreach ($config['stages'] ?? [] as $stage) {
            $originalStatuses = is_array($stage['original']) ? $stage['original'] : [$stage['original']];
            
            // Считаем cumulative: всех клиентов, которые достигли хотя бы одного из статусов этапа
            $reachedCount = $allClients->filter(function ($client) use ($originalStatuses, $excluded) {
                if (!$client->crm_status || in_array($client->crm_status, $excluded)) {
                    return false;
                }
                
                // Проверяем, достиг ли клиент хотя бы одного статуса этого этапа
                return in_array($client->crm_status, $originalStatuses);
            })->count();
            
            // Для cumulative считаем всех, кто достиг хотя бы этого этапа или более продвинутых
            $reachedCount = $allClients->filter(function ($client) use ($originalStatuses, $excluded, $statusStats) {
                if (!$client->crm_status || in_array($client->crm_status, $excluded)) {
                    return false;
                }
                
                $clientStatusIndex = $statusStats->search(function ($s) use ($client) {
                    return $s['status'] === $client->crm_status;
                });
                
                // Проверяем, достиг ли клиент хотя бы одного статуса этого этапа или более продвинутого
                foreach ($originalStatuses as $targetStatus) {
                    $targetIndex = $statusStats->search(function ($s) use ($targetStatus) {
                        return $s['status'] === $targetStatus;
                    });
                    
                    if ($clientStatusIndex !== false && $targetIndex !== false && $clientStatusIndex >= $targetIndex) {
                        return true;
                    }
                }
                
                return false;
            })->count();
            
            $conversion = $totalLeads > 0 
                ? round(($reachedCount / $totalLeads) * 100, 1) 
                : 0;
            
            $funnel[] = [
                'name' => $stage['name'] ?? $this->humanizeStatus($originalStatuses[0]),
                'count' => $reachedCount,
                'conversion' => $conversion,
            ];
        }
        
        // Финальный этап: Заказы
        $clientsWithOrders = count($clientsWithOrdersIds);
        $conversionToOrders = $totalLeads > 0 
            ? round(($clientsWithOrders / $totalLeads) * 100, 1) 
            : 0;
        
        $funnel[] = [
            'name' => 'Заказы',
            'count' => $clientsWithOrders,
            'conversion' => $conversionToOrders,
        ];
        
        return $funnel;
    }
    
    /**
     * Строит полную воронку (все статусы)
     */
    private function buildFunnelFull($allClients, $statusStats, int $totalLeads, array $clientsWithOrdersIds): array
    {
        $funnel = [];
        
        // Этап 1: Все лиды
        $funnel[] = [
            'name' => 'Все лиды',
            'count' => $totalLeads,
            'conversion' => null,
        ];
        
        // Промежуточные этапы: cumulative
        foreach ($statusStats as $stat) {
            $reachedCount = $allClients->filter(function ($client) use ($stat, $statusStats) {
                if (!$client->crm_status) {
                    return false;
                }
                
                $clientStatusIndex = $statusStats->search(function ($s) use ($client) {
                    return $s['status'] === $client->crm_status;
                });
                $targetStatusIndex = $statusStats->search(function ($s) use ($stat) {
                    return $s['status'] === $stat['status'];
                });
                
                return $clientStatusIndex !== false && $targetStatusIndex !== false 
                    && $clientStatusIndex >= $targetStatusIndex;
            })->count();
            
            $conversion = $totalLeads > 0 
                ? round(($reachedCount / $totalLeads) * 100, 1) 
                : 0;
            
            $funnel[] = [
                'name' => $this->humanizeStatus($stat['status']),
                'count' => $reachedCount,
                'conversion' => $conversion,
            ];
        }
        
        // Финальный этап: Заказы
        $clientsWithOrders = count($clientsWithOrdersIds);
        $conversionToOrders = $totalLeads > 0 
            ? round(($clientsWithOrders / $totalLeads) * 100, 1) 
            : 0;
        
        $funnel[] = [
            'name' => 'Заказы',
            'count' => $clientsWithOrders,
            'conversion' => $conversionToOrders,
        ];
        
        return $funnel;
    }
    
    /**
     * Преобразует техническое название статуса в человекочитаемое
     * Автоматически обрабатывает любые статусы без хардкода
     */
    private function humanizeStatus(string $status): string
    {
        // Убираем подчеркивания и делаем первую букву заглавной
        $humanized = str_replace('_', ' ', $status);
        $humanized = str_replace('-', ' ', $humanized);
        
        // Преобразуем каждое слово с заглавной буквы
        $humanized = ucwords(strtolower($humanized));
        
        // Если статус уже на русском или содержит кириллицу - оставляем как есть
        if (preg_match('/[а-яё]/iu', $status)) {
            return $status;
        }
        
        return $humanized;
    }
    
    /**
     * Преобразует техническое название источника в человекочитаемое
     * Автоматически обрабатывает любые источники без хардкода
     */
    private function humanizeSource(string $source): string
    {
        // Убираем подчеркивания и делаем первую букву заглавной
        $humanized = str_replace('_', ' ', $source);
        $humanized = str_replace('-', ' ', $humanized);
        
        // Преобразуем каждое слово с заглавной буквы
        $humanized = ucwords(strtolower($humanized));
        
        // Если источник уже на русском или содержит кириллицу - оставляем как есть
        if (preg_match('/[а-яё]/iu', $source)) {
            return $source;
        }
        
        return $humanized;
    }

    private function calculateChange(float $previous, float $current): float
    {
        if ($previous == 0) {
            return $current > 0 ? 100 : 0;
        }
        return round((($current - $previous) / $previous) * 100, 1);
    }
}
