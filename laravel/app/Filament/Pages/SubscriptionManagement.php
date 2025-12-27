<?php

namespace App\Filament\Pages;

use App\Models\Subscription;
use App\Models\User;
use Filament\Forms;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Tables;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;
use App\Services\SubscriptionService;

class SubscriptionManagement extends Page implements HasForms, HasTable
{
    use InteractsWithForms;
    use InteractsWithTable;

    protected SubscriptionService $subscriptionService;

    protected static ?string $navigationIcon = 'heroicon-o-credit-card';
    protected static string $view = 'filament.pages.subscription-management';
    protected static ?string $navigationLabel = 'Подписка';
    protected static ?int $navigationSort = 10;
    protected static ?string $title = 'Управление подпиской';

    public static function canAccess(): bool
    {
        $user = Auth::user();
        return $user && ($user->isHead() || $user->isOrganizationAdmin());
    }

    public ?array $data = [];
    public ?Subscription $subscription = null;
    public ?int $selectedDepartmentId = null;

    public function boot(): void
    {
        $this->subscriptionService = app(SubscriptionService::class);
    }

    public function mount(): void
    {
        $user = Auth::user();
        
        $departments = \App\Models\Department::where('organization_id', $user->organization_id)
            ->where('is_active', true)
            ->get();

        if ($departments->count() === 1) {
            $this->selectedDepartmentId = $departments->first()->id;
        } elseif ($user->isHead() && $user->department_id) {
            $this->selectedDepartmentId = $user->department_id;
        } else {
            $this->selectedDepartmentId = null;
        }

        $this->loadSubscription();
    }

    public function loadSubscription(): void
    {
        if (!$this->selectedDepartmentId) {
            $this->subscription = null;
            return;
        }

        $department = \App\Models\Department::find($this->selectedDepartmentId);
        if (!$department) {
            $this->subscription = null;
            return;
        }

        $this->subscription = $department->getActiveSubscription();
        
        // Если активной подписки нет, создаем или используем существующую
        if (!$this->subscription) {
            // Проверяем, есть ли вообще какая-то подписка у отдела
            $anySubscription = $department->subscriptions()->orderBy('created_at', 'desc')->first();
            
            if (!$anySubscription) {
                // Если подписки вообще нет, создаем новую истекшую подписку
                $this->subscription = Subscription::create([
                    'department_id' => $this->selectedDepartmentId,
                    'organization_id' => Auth::user()->organization_id,
                    'subscription_plan_id' => \App\Models\SubscriptionPlan::getStandard()->id,
                    'starts_at' => now()->subDay(),
                    'ends_at' => now()->subDay(), // Истекшая, чтобы считалась как "нет подписки"
                    'trial_ends_at' => null,
                    'paid_users_limit' => 0,
                    'monthly_price' => 0,
                    'auto_renew' => false,
                ]);
            } else {
                // Если есть подписка, но она не активна, используем последнюю
                $this->subscription = $anySubscription;
            }
        }

        if ($this->subscription) {
            $this->form->fill([
                'subscription_plan_id' => $this->subscription->subscription_plan_id,
                'paid_users_limit' => max(1, $this->subscription->paid_users_limit),
                'upgrade_users_count' => max(1, $this->subscription->paid_users_limit),
                'months' => 1,
            ]);
        } else {
            $this->form->fill([
                'subscription_plan_id' => \App\Models\SubscriptionPlan::getStandard()->id,
                'paid_users_limit' => 1,
                'upgrade_users_count' => 1,
                'months' => 1,
            ]);
        }
    }

    public function updatedSelectedDepartmentId(): void
    {
        $this->loadSubscription();
    }

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Tabs::make('subscription_tabs')
                    ->tabs([
                        Forms\Components\Tabs\Tab::make('renewal')
                            ->label(function () {
                                $subscription = $this->subscription;
                                if (!$subscription || $subscription->isExpired()) {
                                    return 'Оплатить подписку';
                                }
                                return 'Продление подписки';
                            })
                            ->schema([
                                Forms\Components\Select::make('subscription_plan_id')
                                            ->label('Тарифный план')
                                            ->options(function () {
                                                return \App\Models\SubscriptionPlan::where('is_active', true)
                                                    ->get()
                                                    ->mapWithKeys(function ($plan) {
                                                        return [$plan->id => $plan->name . ' (' . number_format($plan->price_per_user, 0, ',', ' ') . '₽/пользователь)'];
                                                    });
                                            })
                                            ->required()
                                            ->live()
                                            ->default(function () {
                                                return $this->subscription?->subscription_plan_id ?? \App\Models\SubscriptionPlan::getStandard()->id;
                                            })
                                            ->afterStateUpdated(function ($state, Forms\Set $set, Forms\Get $get) {
                                                $subscription = $this->subscription;
                                                if ($state) {
                                                    $plan = \App\Models\SubscriptionPlan::find($state);
                                                    if ($plan) {
                                                        $limit = $get('paid_users_limit') ?? $subscription?->paid_users_limit ?? 1;
                                                        $months = $get('months') ?? 1;
                                                        $newMonthlyPrice = $limit * $plan->price_per_user;
                                                        
                                                        if (!$subscription || $subscription->isTrial() || $subscription->isExpired()) {
                                                            $set('total_price', round($newMonthlyPrice * $months, 2));
                                                            return;
                                                        }
                                                        
                                                        $oldMonthlyPrice = $subscription->monthly_price;
                                                        
                                                        if ($limit == $subscription->paid_users_limit && $state == $subscription->subscription_plan_id) {
                                                            $set('total_price', round($newMonthlyPrice * $months, 2));
                                                        } else {
                                                            if ($subscription->isActive()) {
                                                $daysRemaining = (int) max(0, now()->diffInDays($subscription->ends_at, false));
                                                $priceDifference = $newMonthlyPrice - $oldMonthlyPrice;
                                                $proportionalPrice = $this->subscriptionService->calculateProportionalPrice($priceDifference, $daysRemaining, now());
                                                                $total = round($proportionalPrice + ($newMonthlyPrice * ($months - 1)) + $newMonthlyPrice, 2);
                                                                $set('total_price', $total);
                                                            } else {
                                                                $set('total_price', round($newMonthlyPrice * $months, 2));
                                                            }
                                                        }
                                                    }
                                                }
                                            })
                                            ->helperText('Выберите тарифный план'),

                        Forms\Components\TextInput::make('paid_users_limit')
                            ->label('Количество пользователей')
                            ->numeric()
                            ->required()
                            ->minValue(function () {
                                $subscription = $this->subscription;
                                return $subscription ? max(1, $subscription->paid_users_limit) : 1;
                            })
                            ->default(function () {
                                $subscription = $this->subscription;
                                return $subscription ? max(1, $subscription->paid_users_limit) : 1;
                            })
                            ->live()
                            ->afterStateUpdated(function ($state, Forms\Set $set, Forms\Get $get) {
                                $subscription = $this->subscription;
                                                $planId = $get('subscription_plan_id');
                                                if ($planId && $state > 0) {
                                                    $plan = \App\Models\SubscriptionPlan::find($planId);
                                                    if ($plan) {
                                                        $months = $get('months') ?? 1;
                                                        $newMonthlyPrice = $state * $plan->price_per_user;
                                                        
                                                        if (!$subscription || $subscription->isTrial() || $subscription->isExpired()) {
                                                            $set('total_price', round($newMonthlyPrice * $months, 2));
                                                            return;
                                                        }
                                                        
                                                        $oldMonthlyPrice = $subscription->monthly_price;
                                                        
                                                        if ($state == $subscription->paid_users_limit && $planId == $subscription->subscription_plan_id) {
                                                            $set('total_price', round($newMonthlyPrice * $months, 2));
                                                        } else {
                                                            if ($subscription->isActive()) {
                                                $daysRemaining = (int) max(0, now()->diffInDays($subscription->ends_at, false));
                                                $priceDifference = $newMonthlyPrice - $oldMonthlyPrice;
                                                $proportionalPrice = $this->subscriptionService->calculateProportionalPrice($priceDifference, $daysRemaining, now());
                                                                $total = round($proportionalPrice + ($newMonthlyPrice * ($months - 1)) + $newMonthlyPrice, 2);
                                                                $set('total_price', $total);
                                                            } else {
                                                                $set('total_price', round($newMonthlyPrice * $months, 2));
                                                            }
                                                        }
                                                    }
                                                }
                                            })
                            ->helperText(function () {
                                $subscription = $this->subscription;
                                if (!$subscription) {
                                    return 'Количество пользователей для продления подписки';
                                }
                                $paidCount = $subscription->paid_users_limit;
                                return "Минимум: {$paidCount} (оплачено пользователей в отделе: {$paidCount})";
                            }),

                                        Forms\Components\Select::make('months')
                                            ->label('Период продления')
                                            ->options([
                                                1 => '1 месяц',
                                                2 => '2 месяца',
                                                3 => '3 месяца',
                                                6 => '6 месяцев',
                                                12 => '12 месяцев',
                                            ])
                                            ->required()
                                            ->default(1)
                                            ->live()
                                            ->afterStateUpdated(function ($state, Forms\Set $set, Forms\Get $get) {
                                                $subscription = $this->subscription;
                                                $planId = $get('subscription_plan_id');
                                                if ($planId) {
                                                    $plan = \App\Models\SubscriptionPlan::find($planId);
                                                    if ($plan) {
                                                        $limit = $get('paid_users_limit') ?? $subscription?->paid_users_limit ?? 1;
                                                        $months = $get('months') ?? 1;
                                                        $newMonthlyPrice = $limit * $plan->price_per_user;
                                                        
                                                        if (!$subscription || $subscription->isTrial() || $subscription->isExpired()) {
                                                            $set('total_price', round($newMonthlyPrice * $months, 2));
                                                            return;
                                                        }
                                                        
                                                        $oldMonthlyPrice = $subscription->monthly_price;
                                                        
                                                        if ($limit == $subscription->paid_users_limit && $planId == $subscription->subscription_plan_id) {
                                                            $set('total_price', round($newMonthlyPrice * $months, 2));
                                                        } else {
                                                            if ($subscription->isActive()) {
                                                $daysRemaining = (int) max(0, now()->diffInDays($subscription->ends_at, false));
                                                $priceDifference = $newMonthlyPrice - $oldMonthlyPrice;
                                                $proportionalPrice = $this->subscriptionService->calculateProportionalPrice($priceDifference, $daysRemaining, now());
                                                                $total = round($proportionalPrice + ($newMonthlyPrice * ($months - 1)) + $newMonthlyPrice, 2);
                                                                $set('total_price', $total);
                                                            } else {
                                                                $set('total_price', round($newMonthlyPrice * $months, 2));
                                                            }
                                                        }
                                                    }
                                                }
                                            })
                                            ->helperText('На какой период продлить подписку'),

                                        Forms\Components\Placeholder::make('plan_info')
                                            ->label('Информация о тарифе')
                                            ->content(function (Forms\Get $get) {
                                                $planId = $get('subscription_plan_id');
                                                if (!$planId) {
                                                    return 'Выберите тарифный план';
                                                }
                                                
                                                $plan = \App\Models\SubscriptionPlan::find($planId);
                                                if (!$plan) {
                                                    return 'Тариф не найден';
                                                }
                                                
                                                $features = [];
                                                if ($plan->ai_analytics_enabled) {
                                                    $features[] = '✓ AI-аналитика';
                                                }
                                                if ($plan->crm_sync_enabled) {
                                                    $features[] = '✓ Синхронизация с BlueSales';
                                                }
                                                
                                                return $plan->description . "\n" . implode("\n", $features);
                                            })
                                            ->columnSpanFull(),

                                        Forms\Components\TextInput::make('total_price')
                                            ->label('Итоговая сумма к оплате')
                                            ->disabled()
                                            ->prefix('₽')
                                            ->formatStateUsing(function ($state) {
                                                if (!$state) {
                                                    return '0,00';
                                                }
                                                $value = round((float)$state, 2);
                                                return number_format($value, 2, ',', ' ');
                                            })
                                            ->dehydrated(false)
                                            ->afterStateHydrated(function (Forms\Set $set, Forms\Get $get) {
                                                $subscription = $this->subscription;
                                                // Пересчитываем цену после загрузки формы
                                                $planId = $get('subscription_plan_id') ?? $subscription?->subscription_plan_id ?? \App\Models\SubscriptionPlan::getStandard()->id;
                                                $limit = $get('paid_users_limit') ?? $subscription?->paid_users_limit ?? 1;
                                                $months = $get('months') ?? 1;
                                                
                                                if (!$planId || !$subscription) {
                                                    $set('total_price', 0);
                                                    return;
                                                }
                                                
                                                $totalPrice = $this->subscriptionService->calculateTotalPrice($subscription, $limit, $planId, $months);
                                                $set('total_price', $totalPrice);
                                            })
                                            ->default(function (Forms\Get $get) {
                                                $subscription = $this->subscription;
                                                $planId = $get('subscription_plan_id') ?? $subscription?->subscription_plan_id ?? \App\Models\SubscriptionPlan::getStandard()->id;
                                                $limit = $get('paid_users_limit') ?? $subscription?->paid_users_limit ?? 1;
                                                $months = $get('months') ?? 1;
                                                
                                                if (!$planId || !$subscription) {
                                                    return 0;
                                                }
                                                
                                                $plan = \App\Models\SubscriptionPlan::find($planId);
                                                if (!$plan) {
                                                    return 0;
                                                }
                                                
                                                $newMonthlyPrice = $limit * $plan->price_per_user;
                                                $oldMonthlyPrice = $subscription->monthly_price;
                                                
                                                if ($limit == $subscription->paid_users_limit && $planId == $subscription->subscription_plan_id) {
                                                    return $newMonthlyPrice * $months;
                                                }
                                                
                                $daysRemaining = (int) max(0, now()->diffInDays($subscription->ends_at, false));
                                $priceDifference = $newMonthlyPrice - $oldMonthlyPrice;
                                $proportionalPrice = $this->subscriptionService->calculateProportionalPrice($priceDifference, $daysRemaining, now());
                                                
                                                return $proportionalPrice + ($newMonthlyPrice * ($months - 1)) + $newMonthlyPrice;
                                            })
                                            ->helperText(function (Forms\Get $get) {
                                                $subscription = $this->subscription;
                                                if (!$subscription) {
                                                    return 'Заполните все поля';
                                                }
                                                
                                                $planId = $get('subscription_plan_id') ?? $subscription->subscription_plan_id ?? \App\Models\SubscriptionPlan::getStandard()->id;
                                                $limit = $get('paid_users_limit') ?? $subscription->paid_users_limit ?? 1;
                                                $months = $get('months') ?? 1;
                                                
                                                $plan = \App\Models\SubscriptionPlan::find($planId);
                                                if (!$plan) {
                                                    return 'Заполните все поля';
                                                }
                                                
                                                $newMonthlyPrice = $limit * $plan->price_per_user;
                                                
                                                // Если нет активной подписки или она пробная/истекла - просто полная оплата
                                                if (!$subscription || $subscription->isTrial() || $subscription->isExpired()) {
                                                    $html = '<div class="text-sm space-y-1">';
                                                    $html .= '<div>Тариф: <span class="font-medium">' . $plan->name . '</span></div>';
                                                    $html .= '<div>Пользователей: <span class="font-medium">' . $limit . '</span></div>';
                                                    $html .= '<div>Цена за пользователя: <span class="font-medium">' . number_format($plan->price_per_user, 2, ',', ' ') . '₽</span></div>';
                                                    $html .= '<div>Период: <span class="font-medium">' . $months . ' ' . ($months == 1 ? 'месяц' : ($months < 5 ? 'месяца' : 'месяцев')) . '</span></div>';
                                                    $html .= '</div>';
                                                    return new \Illuminate\Support\HtmlString($html);
                                                }
                                                
                                                $oldMonthlyPrice = $subscription->monthly_price;
                                                
                                                // Продление на тех же условиях
                                                if ($limit == $subscription->paid_users_limit && $planId == $subscription->subscription_plan_id) {
                                                    $html = '<div class="text-sm space-y-1">';
                                                    $html .= '<div class="font-semibold">Продление подписки</div>';
                                                    $html .= '<div>Тариф: <span class="font-medium">' . $plan->name . '</span></div>';
                                                    $html .= '<div>Пользователей: <span class="font-medium">' . $limit . '</span></div>';
                                                    $html .= '<div>Цена за пользователя: <span class="font-medium">' . number_format($plan->price_per_user, 2, ',', ' ') . '₽</span></div>';
                                                    $html .= '<div>Период: <span class="font-medium">' . $months . ' ' . ($months == 1 ? 'месяц' : ($months < 5 ? 'месяца' : 'месяцев')) . '</span></div>';
                                                    $html .= '</div>';
                                                    return new \Illuminate\Support\HtmlString($html);
                                                }
                                                
                                                // Апгрейд: пропорциональная доплата только если подписка активна
                                                if ($subscription->isActive()) {
                                                    $daysRemaining = (int) max(0, now()->diffInDays($subscription->ends_at, false));
                                                    $priceDifference = $newMonthlyPrice - $oldMonthlyPrice;
                                                    $proportionalPrice = $this->subscriptionService->calculateProportionalPrice($priceDifference, $daysRemaining, now());
                                                    $futureMonths = round($newMonthlyPrice * ($months - 1), 2);
                                                    $total = round($proportionalPrice + $futureMonths + $newMonthlyPrice, 2);
                                                    
                                                    $html = '<div class="text-sm space-y-1">';
                                                    $html .= '<div class="font-semibold">Апгрейд подписки</div>';
                                                    $html .= '<div class="border-t border-gray-300 my-2"></div>';
                                                    $html .= '<div class="font-medium">Оплата будущих периодов</div>';
                                                    $html .= '<div class="pl-4 space-y-0.5 text-xs">';
                                                    $html .= '<div>Тариф: <span class="font-medium">' . $plan->name . '</span></div>';
                                                    $html .= '<div>Пользователей: <span class="font-medium">' . $limit . '</span></div>';
                                                    $html .= '<div>Цена за пользователя: <span class="font-medium">' . number_format($plan->price_per_user, 2, ',', ' ') . '₽</span></div>';
                                                    $html .= '<div>Период: <span class="font-medium">' . $months . ' ' . ($months == 1 ? 'месяц' : ($months < 5 ? 'месяца' : 'месяцев')) . '</span></div>';
                                                    $html .= '<div>Сумма: <span class="font-medium">' . number_format($futureMonths + $newMonthlyPrice, 2, ',', ' ') . '₽</span></div>';
                                                    $html .= '</div>';
                                                    if ($proportionalPrice > 0) {
                                                        $oldUsersCount = $subscription->paid_users_limit;
                                                        $newUsersCount = $limit;
                                                        $addedUsers = $newUsersCount - $oldUsersCount;
                                                        
                                                        $html .= '<div class="font-medium mt-2">Доплата за неполный период</div>';
                                                        $html .= '<div class="pl-4 space-y-0.5 text-xs">';
                                                        if ($addedUsers > 0) {
                                                            $pricePerUser = round($proportionalPrice / $addedUsers, 2);
                                                            $html .= '<div>Пользователей: <span class="font-medium">' . $addedUsers . '</span></div>';
                                                            $html .= '<div>Цена за пользователя: <span class="font-medium">' . number_format($pricePerUser, 2, ',', ' ') . '₽</span></div>';
                                                            $html .= '<div>Доплата: <span class="font-medium">' . number_format($proportionalPrice, 2, ',', ' ') . '₽</span> (<span class="font-medium">' . $daysRemaining . ' дн.</span>)</div>';
                                                        } else {
                                                            $html .= '<div>Доплата за изменение тарифа (<span class="font-medium">' . $daysRemaining . ' дн.</span>): <span class="font-medium">' . number_format($proportionalPrice, 2, ',', ' ') . '₽</span></div>';
                                                        }
                                                        $html .= '</div>';
                                                    }
                                                    $html .= '</div>';
                                                    return new \Illuminate\Support\HtmlString($html);
                                                } else {
                                                    $html = '<div class="text-sm space-y-1">';
                                                    $html .= '<div>Тариф: <span class="font-medium">' . $plan->name . '</span></div>';
                                                    $html .= '<div>Пользователей: <span class="font-medium">' . $limit . '</span></div>';
                                                    $html .= '<div>Цена за пользователя: <span class="font-medium">' . number_format($plan->price_per_user, 2, ',', ' ') . '₽</span></div>';
                                                    $html .= '<div>Период: <span class="font-medium">' . $months . ' ' . ($months == 1 ? 'месяц' : ($months < 5 ? 'месяца' : 'месяцев')) . '</span></div>';
                                                    $html .= '</div>';
                                                    return new \Illuminate\Support\HtmlString($html);
                                                }
                                            })
                                            ->columnSpanFull(),

                                        Forms\Components\Actions::make([
                                            Forms\Components\Actions\Action::make('pay')
                                                ->label('Оплатить')
                                                ->icon('heroicon-o-credit-card')
                                                ->color('success')
                                                ->requiresConfirmation()
                                                ->modalHeading('Оплата подписки')
                                                ->modalDescription('Вы уверены, что хотите оплатить подписку?')
                                                ->action('pay'),
                                        ])
                                            ->columnSpanFull(),
                            ]),
                        Forms\Components\Tabs\Tab::make('upgrade')
                            ->label('Добавить пользователей на оплаченный период')
                            ->schema([
                                Forms\Components\TextInput::make('upgrade_users_count')
                                            ->label('Новое общее количество пользователей')
                                            ->numeric()
                                            ->required()
                            ->minValue(function () {
                                $subscription = $this->subscription;
                                return $subscription ? max(1, $subscription->paid_users_limit) : 1;
                            })
                            ->default(function () {
                                $subscription = $this->subscription;
                                return $subscription ? max(1, $subscription->paid_users_limit) : 1;
                            })
                            ->live()
                            ->helperText(function () {
                                $subscription = $this->subscription;
                                if (!$subscription || !$subscription->isActive()) {
                                    return 'Доступно только для активной подписки';
                                }
                                $paidCount = $subscription->paid_users_limit;
                                return "Минимум: {$paidCount} (оплачено пользователей в отделе: {$paidCount}). Укажите новое количество (больше текущего для добавления пользователей)";
                            })
                                            ->afterStateUpdated(function ($state, Forms\Set $set, Forms\Get $get) {
                                $subscription = $this->subscription;
                                                if (!$subscription || !$subscription->isActive()) {
                                                    $set('upgrade_total_price', 0);
                                                    return;
                                                }
                                                
                                                if ($state <= $subscription->paid_users_limit) {
                                                    $set('upgrade_total_price', 0);
                                                    return;
                                                }
                                                
                                                $plan = $subscription->plan;
                                                if (!$plan) {
                                                    $set('upgrade_total_price', 0);
                                                    return;
                                                }
                                                
                                                $addedUsers = $state - $subscription->paid_users_limit;
                                                $pricePerUser = $plan->price_per_user;
                                                
                                                $daysRemaining = (int) max(0, now()->diffInDays($subscription->ends_at, false));
                                                $monthlyPriceForAddedUsers = $addedUsers * $pricePerUser;
                                                $proportionalPrice = $this->subscriptionService->calculateProportionalPrice($monthlyPriceForAddedUsers, $daysRemaining, now());
                                                $set('upgrade_total_price', $proportionalPrice);
                                            }),

                                        Forms\Components\Placeholder::make('upgrade_info')
                                            ->label('Информация о доплате')
                                            ->content(function (Forms\Get $get) {
                                                $subscription = $this->subscription;
                                                if (!$subscription || !$subscription->isActive()) {
                                                    return 'Доступно только для активной подписки';
                                                }
                                                
                                                $newCount = $get('upgrade_users_count') ?? $subscription->paid_users_limit;
                                                if ($newCount <= $subscription->paid_users_limit) {
                                                    return 'Укажите количество больше текущего (' . $subscription->paid_users_limit . ')';
                                                }
                                                
                                                $addedUsers = $newCount - $subscription->paid_users_limit;
                                                $plan = $subscription->plan;
                                                $pricePerUser = $plan->price_per_user;
                                                
                                                $daysRemaining = (int) max(0, now()->diffInDays($subscription->ends_at, false));
                                                $monthlyPriceForAddedUsers = $addedUsers * $pricePerUser;
                                                $proportionalPrice = $this->subscriptionService->calculateProportionalPrice($monthlyPriceForAddedUsers, $daysRemaining, now());
                                                
                                                // Рассчитываем количество месяцев
                                                $monthsCount = round($daysRemaining / 30, 1);
                                                $periodText = $monthsCount > 0 
                                                    ? number_format($monthsCount, 1, ',', ' ') . ' ' . ($monthsCount == 1 ? 'месяц' : ($monthsCount < 5 ? 'месяца' : 'месяцев')) . ' (' . $daysRemaining . ' дн.)'
                                                    : $daysRemaining . ' дн.';
                                                
                                                $html = '<div class="text-sm space-y-1">';
                                                $html .= '<div>Добавляется пользователей: <span class="font-medium">+' . $addedUsers . '</span></div>';
                                                $html .= '<div>Цена за пользователя: <span class="font-medium">' . number_format($pricePerUser, 2, ',', ' ') . '₽/месяц</span></div>';
                                                $html .= '<div>Период доплаты: <span class="font-medium">' . $periodText . '</span></div>';
                                                $html .= '<div class="border-t border-gray-300 my-2"></div>';
                                                $html .= '<div class="font-semibold">Итого к доплате: ' . number_format($proportionalPrice, 2, ',', ' ') . '₽</div>';
                                                $html .= '</div>';
                                                
                                                return new \Illuminate\Support\HtmlString($html);
                                            })
                                            ->columnSpanFull(),

                                        Forms\Components\TextInput::make('upgrade_total_price')
                                            ->label('Итоговая сумма к доплате')
                                            ->disabled()
                                            ->prefix('₽')
                                            ->formatStateUsing(function ($state) {
                                                if (!$state) {
                                                    return '0,00';
                                                }
                                                $value = round((float)$state, 2);
                                                return number_format($value, 2, ',', ' ');
                                            })
                                            ->dehydrated(false)
                                            ->default(0),
                                        
                                        Forms\Components\Actions::make([
                                            Forms\Components\Actions\Action::make('pay_upgrade')
                                                ->label('Оплатить')
                                                ->icon('heroicon-o-credit-card')
                                                ->color('success')
                                                ->requiresConfirmation()
                                                ->modalHeading('Оплата апгрейда')
                                                ->modalDescription('Вы уверены, что хотите оплатить добавление пользователей к текущей подписке?')
                                                ->action('payUpgrade'),
                                        ])
                                            ->columnSpanFull(),
                            ])
                            ->visible(function () {
                                $subscription = $this->subscription;
                                return $subscription && $subscription->isActive() && !$subscription->isTrial();
                            }),
                    ])
                    ->columnSpanFull(),
            ])
            ->statePath('data');
    }

    public function table(Table $table): Table
    {
        $subscription = $this->subscription;
        
        return $table
            ->query(function () {
                if (!$this->selectedDepartmentId) {
                    // Если отдел не выбран, возвращаем пустой запрос
                    return User::query()->whereRaw('1 = 0');
                }
                
                return User::query()
                    ->where('department_id', $this->selectedDepartmentId);
            })
            ->columns([
                Tables\Columns\TextColumn::make('full_name')
                    ->label('Фамилия Имя')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('email')
                    ->label('Email')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\IconColumn::make('is_active')
                    ->label('Активен')
                    ->boolean()
                    ->trueIcon('heroicon-o-check-circle')
                    ->falseIcon('heroicon-o-x-circle')
                    ->trueColor('success')
                    ->falseColor('danger')
                    ->getStateUsing(fn (?User $record) => $record?->is_active ?? false),

                Tables\Columns\TextColumn::make('activated_at')
                    ->label('Активирован')
                    ->dateTime('d.m.Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\TernaryFilter::make('is_active')
                    ->label('Только активные')
                    ->placeholder('Все пользователи')
                    ->trueLabel('Только активные')
                    ->falseLabel('Только неактивные'),
            ])
            ->actions([
                Tables\Actions\Action::make('activate')
                    ->label('Активировать')
                    ->icon('heroicon-o-check-circle')
                    ->color('success')
                    ->requiresConfirmation()
                    ->visible(fn (?User $record) => $record && !$record->is_active && $this->canActivateUser())
                    ->action(function (?User $record) {
                        if (!$record) {
                            return;
                        }
                        $record->update([
                            'is_active' => true,
                            'activated_at' => now(),
                        ]);

                        Notification::make()
                            ->success()
                            ->title('Пользователь активирован')
                            ->body($record->full_name . ' получил платный доступ.')
                            ->send();
                    }),

                Tables\Actions\Action::make('deactivate')
                    ->label('Деактивировать')
                    ->icon('heroicon-o-x-circle')
                    ->color('danger')
                    ->requiresConfirmation()
                    ->visible(fn (?User $record) => $record && $record->is_active)
                    ->action(function (?User $record) {
                        if (!$record) {
                            return;
                        }
                        $record->update([
                            'is_active' => false,
                            'activated_at' => null,
                        ]);

                        Notification::make()
                            ->success()
                            ->title('Пользователь деактивирован')
                            ->body($record->full_name . ' больше не имеет платного доступа.')
                            ->send();
                    }),
            ])
            ->defaultSort('created_at', 'desc');
    }

    public function pay(): void
    {
        $data = $this->form->getState();
        $newLimit = (int) $data['paid_users_limit'];
        $newPlanId = $data['subscription_plan_id'];
        $months = (int) $data['months'];
        
        $plan = \App\Models\SubscriptionPlan::find($newPlanId);
        $monthlyPrice = $newLimit * $plan->price_per_user;
        $totalPrice = round($monthlyPrice * $months, 2);

        $currentSubscription = $this->subscription;
        
        // Если подписки нет, создаем её перед оплатой
        if (!$currentSubscription) {
            $department = \App\Models\Department::find($this->selectedDepartmentId);
            if (!$department) {
                Notification::make()
                    ->danger()
                    ->title('Ошибка')
                    ->body('Отдел не найден.')
                    ->send();
                return;
            }
            
            // Создаем новую истекшую подписку
            $currentSubscription = Subscription::create([
                'department_id' => $this->selectedDepartmentId,
                'organization_id' => Auth::user()->organization_id,
                'subscription_plan_id' => $newPlanId,
                'starts_at' => now()->subDay(),
                'ends_at' => now()->subDay(), // Истекшая, чтобы считалась как "нет подписки"
                'trial_ends_at' => null,
                'paid_users_limit' => 0,
                'monthly_price' => 0,
                'auto_renew' => false,
            ]);
            
            $this->subscription = $currentSubscription;
        }
        
        $activeUsersCount = $currentSubscription->getActivePaidUsersCount();
        
        // Если подписка пробная или истекла - просто создаем/обновляем подписку без пропорциональной доплаты
        $isTrialOrExpired = $currentSubscription->isTrial() || $currentSubscription->isExpired();
        
        if (!$isTrialOrExpired) {
            // Проверка даунгрейда только для активных подписок
            $isDowngrade = $newLimit < $currentSubscription->paid_users_limit 
                || $newPlanId != $currentSubscription->subscription_plan_id;
            
            if ($isDowngrade && $currentSubscription->ends_at->isFuture()) {
                Notification::make()
                    ->warning()
                    ->title('Даунгрейд недоступен')
                    ->body('Уменьшение тарифа или количества пользователей возможно только после окончания оплаченного периода.')
                    ->send();
                return;
            }
        }

        if ($newLimit < $activeUsersCount) {
            Notification::make()
                ->warning()
                ->title('Невозможно уменьшить лимит')
                ->body("У вас активировано {$activeUsersCount} пользователей. Лимит не может быть меньше этого значения.")
                ->send();
            return;
        }

        // TODO: Создать платеж в payments и перейти на страницу оплаты
        $result = $this->subscriptionService->pay(
            $currentSubscription,
            $newLimit,
            $newPlanId,
            $months
        );

        if (!$result['success']) {
            Notification::make()
                ->danger()
                ->title('Ошибка оплаты')
                ->body($result['message'])
                ->send();
            return;
        }

        // Если есть payment_url - редирект на страницу оплаты выбранного шлюза
        if (isset($result['payment_url'])) {
            $this->redirect($result['payment_url']);
            return;
        }

        // Fallback на старое поведение (если вдруг нет payment_url)
        Notification::make()
            ->success()
            ->title('Подписка оплачена')
            ->body($result['message'])
            ->send();
        
        $this->loadSubscription();
    }

    public function payAllDepartments(): void
    {
        $user = Auth::user();
        
        if (!$user->isOrganizationAdmin() && !$user->isHead()) {
            Notification::make()
                ->danger()
                ->title('Доступ запрещен')
                ->body('Только администратор организации или руководитель отдела может оплачивать все отделы.')
                ->send();
            return;
        }

        $departments = \App\Models\Department::where('organization_id', $user->organization_id)
            ->where('is_active', true)
            ->with('subscription')
            ->get();

        $totalAmount = 0;
        $departmentsToPay = [];

        foreach ($departments as $department) {
            $subscription = $department->getActiveSubscription();
            if ($subscription && $subscription->monthly_price > 0) {
                $totalAmount += $subscription->monthly_price;
                $departmentsToPay[] = [
                    'department' => $department->name,
                    'amount' => $subscription->monthly_price,
                ];
            }
        }

        if (empty($departmentsToPay)) {
            Notification::make()
                ->warning()
                ->title('Нет отделов для оплаты')
                ->body('У всех отделов лимит пользователей равен 0 или подписки не настроены.')
                ->send();
            return;
        }

        // TODO: Создать групповой платеж и перейти на страницу оплаты
        // Пока имитируем оплату
        foreach ($departments as $department) {
            $subscription = $department->getActiveSubscription();
            if ($subscription && $subscription->monthly_price > 0) {
                if ($subscription->isTrial()) {
                    $subscription->update(['trial_ends_at' => null]);
                }
                $subscription->update([
                    'ends_at' => $subscription->ends_at->copy()->addMonth(),
                ]);
            }
        }

        $departmentsList = collect($departmentsToPay)->pluck('department')->implode(', ');

        Notification::make()
            ->success()
            ->title('Все отделы оплачены')
            ->body("Оплачено {$departments->count()} отделов. Общая сумма: " . number_format($totalAmount, 2, ',', ' ') . "₽")
            ->send();
    }

    public function payUpgrade(): void
    {
        $data = $this->form->getState();
        $newUsersCount = (int) ($data['upgrade_users_count'] ?? 0);
        
        $currentSubscription = $this->subscription;
        
        if (!$currentSubscription) {
            Notification::make()->danger()->title('Ошибка')->body('Подписка не найдена.')->send();
            return;
        }

        $result = $this->subscriptionService->payUpgrade($currentSubscription, $newUsersCount);

        if (!$result['success']) {
            Notification::make()
                ->warning()
                ->title('Ошибка')
                ->body($result['message'])
                ->send();
            return;
        }

        // Если есть payment_url - редирект на страницу оплаты выбранного шлюза
        if (isset($result['payment_url'])) {
            $this->redirect($result['payment_url']);
            return;
        }
        
        Notification::make()
            ->success()
            ->title('Апгрейд оплачен')
            ->body($result['message'])
            ->send();
        
        $this->loadSubscription();
    }

    protected function canActivateUser(): bool
    {
        if (!$this->subscription) {
            return false;
        }

        $activeUsersCount = $this->subscription->getActivePaidUsersCount();
        return $activeUsersCount < $this->subscription->paid_users_limit;
    }

    public function getSubscriptionStatus(): array
    {
        if (!$this->subscription) {
            return [
                'status_label' => 'Тариф не выбран',
                'ends_at' => null,
                'trial_ends_at' => null,
                'paid_users_limit' => 0,
                'active_users_count' => 0,
                'monthly_price' => 0,
                'price_per_user' => 0,
                'plan_name' => 'Не выбран',
            ];
        }

        $subscription = $this->subscription;
        $statusLabel = $subscription->isTrial() ? 'Пробный период' : ($subscription->isActive() ? 'Активна' : 'Истекла');

        return [
            'status_label' => $statusLabel,
            'ends_at' => $subscription->ends_at,
            'trial_ends_at' => $subscription->trial_ends_at,
            'paid_users_limit' => $subscription->paid_users_limit,
            'active_users_count' => $subscription->getActivePaidUsersCount(),
            'monthly_price' => $subscription->monthly_price,
            'price_per_user' => $subscription->plan->price_per_user ?? 500,
            'plan_name' => $subscription->plan->name ?? 'Не выбран',
        ];
    }

}