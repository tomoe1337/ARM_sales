<?php

namespace App\Services;

use App\Models\Subscription;
use App\Models\SubscriptionPlan;
use App\Models\Department;
use App\Services\PaymentGateway\PaymentGatewayFactory;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Exception;

class SubscriptionService
{
    /**
     * Рассчитывает пропорциональную стоимость с учетом реальных дней в каждом месяце
     * 
     * @param float $monthlyPrice Цена за месяц
     * @param int $daysRemaining Количество оставшихся дней
     * @param Carbon $startDate Дата начала расчета (обычно now())
     * @return float Пропорциональная стоимость
     */
    public function calculateProportionalPrice(float $monthlyPrice, int $daysRemaining, Carbon $startDate): float
    {
        if ($daysRemaining <= 0) {
            return 0;
        }

        $totalPrice = 0;
        $currentDate = $startDate->copy();
        $remainingDays = $daysRemaining;

        while ($remainingDays > 0) {
            // Количество дней в текущем месяце
            $daysInCurrentMonth = $currentDate->daysInMonth;
            
            // Количество дней, которые нужно учесть в этом месяце
            $daysToUse = min($remainingDays, $daysInCurrentMonth);
            
            // Пропорциональная стоимость за этот месяц
            $monthlyProportional = ($monthlyPrice / $daysInCurrentMonth) * $daysToUse;
            $totalPrice += $monthlyProportional;
            
            // Переходим к следующему месяцу
            $remainingDays -= $daysToUse;
            $currentDate->addMonth();
        }

        return round($totalPrice, 2);
    }

    /**
     * Рассчитывает итоговую цену подписки
     * 
     * @param Subscription $subscription Текущая подписка
     * @param int $newLimit Новый лимит пользователей
     * @param int $newPlanId ID нового тарифного плана
     * @param int $months Количество месяцев
     * @return float Итоговая цена
     */
    public function calculateTotalPrice(Subscription $subscription, int $newLimit, int $newPlanId, int $months): float
    {
        $plan = SubscriptionPlan::find($newPlanId);
        if (!$plan) {
            return 0;
        }

        $newMonthlyPrice = $newLimit * $plan->price_per_user;

        // Если нет активной подписки или она пробная/истекла - просто полная оплата
        if ($subscription->isTrial() || $subscription->isExpired()) {
            return round($newMonthlyPrice * $months, 2);
        }

        $oldMonthlyPrice = $subscription->monthly_price;

        // Продление на тех же условиях
        if ($newLimit == $subscription->paid_users_limit && $newPlanId == $subscription->subscription_plan_id) {
            return round($newMonthlyPrice * $months, 2);
        }

        // Апгрейд: пропорциональная доплата только если подписка активна
        if ($subscription->isActive()) {
            $daysRemaining = (int) max(0, now()->diffInDays($subscription->ends_at, false));
            $priceDifference = $newMonthlyPrice - $oldMonthlyPrice;
            $proportionalPrice = $this->calculateProportionalPrice($priceDifference, $daysRemaining, now());
            return round($proportionalPrice + ($newMonthlyPrice * ($months - 1)) + $newMonthlyPrice, 2);
        }

        return round($newMonthlyPrice * $months, 2);
    }

    /**
     * Оплата подписки
     * 
     * @param Subscription $subscription Текущая подписка
     * @param int $newLimit Новый лимит пользователей
     * @param int $newPlanId ID нового тарифного плана
     * @param int $months Количество месяцев
     * @return array Результат операции ['success' => bool, 'message' => string]
     */
    public function pay(Subscription $subscription, int $newLimit, int $newPlanId, int $months): array
    {
        $plan = SubscriptionPlan::find($newPlanId);
        if (!$plan) {
            return ['success' => false, 'message' => 'Тарифный план не найден.'];
        }

        $monthlyPrice = $newLimit * $plan->price_per_user;
        $activeUsersCount = $subscription->getActivePaidUsersCount();

        // Если подписка пробная или истекла - просто создаем/обновляем подписку без пропорциональной доплаты
        $isTrialOrExpired = $subscription->isTrial() || $subscription->isExpired();

        if (!$isTrialOrExpired) {
            // Проверка даунгрейда только для активных подписок
            $isDowngrade = $newLimit < $subscription->paid_users_limit 
                || $newPlanId != $subscription->subscription_plan_id;

            if ($isDowngrade && $subscription->ends_at->isFuture()) {
                return [
                    'success' => false,
                    'message' => 'Уменьшение тарифа или количества пользователей возможно только после окончания оплаченного периода.'
                ];
            }
        }

        if ($newLimit < $activeUsersCount) {
            return [
                'success' => false,
                'message' => "У вас активировано {$activeUsersCount} пользователей. Лимит не может быть меньше этого значения."
            ];
        }

        // Определяем: продление на тех же условиях или апгрейд
        $isSameConditions = $newLimit == $subscription->paid_users_limit 
            && $newPlanId == $subscription->subscription_plan_id;

        // Рассчитываем итоговую цену
        $totalPrice = $this->calculateTotalPrice($subscription, $newLimit, $newPlanId, $months);

        try {
            // Создаем платеж через выбранный шлюз (по умолчанию robokassa)
            $gatewayName = config('services.payment.gateway', 'robokassa');
            $gateway = PaymentGatewayFactory::create($gatewayName);
            
            $payment = $gateway->createPayment([
                'subscription_id' => $subscription->id,
                'amount' => $totalPrice,
                'description' => "Подписка на {$months} мес. для {$newLimit} польз.",
                'months' => $months,
                'user_limit' => $newLimit,
                'plan_id' => $newPlanId,
            ]);

            // НЕ обновляем подписку здесь! Это произойдет в webhook после оплаты
            // Подписка будет активирована в RobokassaGateway::handleCallback()

            // Получаем URL для редиректа
            $paymentUrl = $payment->provider_data['payment_url'];

            return [
                'success' => true,
                'message' => 'Перенаправление на оплату',
                'payment_url' => $paymentUrl,
                'payment_id' => $payment->id,
            ];

        } catch (Exception $e) {
            return [
                'success' => false,
                'message' => 'Ошибка создания платежа: ' . $e->getMessage(),
            ];
        }
    }

    /**
     * Апгрейд пользователей для текущего периода (без продления)
     * 
     * @param Subscription $subscription Текущая подписка
     * @param int $newUsersCount Новое общее количество пользователей
     * @return array Результат операции ['success' => bool, 'message' => string, 'price' => float]
     */
    public function payUpgrade(Subscription $subscription, int $newUsersCount): array
    {
        if (!$subscription->isActive() || $subscription->isTrial()) {
            return [
                'success' => false,
                'message' => 'Апгрейд доступен только для активной оплаченной подписки.'
            ];
        }

        if ($newUsersCount <= $subscription->paid_users_limit) {
            return [
                'success' => false,
                'message' => "Новое количество пользователей должно быть больше текущего ({$subscription->paid_users_limit})."
            ];
        }

        $plan = $subscription->plan;
        if (!$plan) {
            return ['success' => false, 'message' => 'Тарифный план не найден.'];
        }

        $addedUsers = $newUsersCount - $subscription->paid_users_limit;
        $pricePerUser = $plan->price_per_user;

        $daysRemaining = (int) max(0, now()->diffInDays($subscription->ends_at, false));

        // Расчет пропорциональной стоимости с учетом реальных дней в каждом месяце
        $monthlyPriceForAddedUsers = $addedUsers * $pricePerUser;
        $proportionalPrice = $this->calculateProportionalPrice($monthlyPriceForAddedUsers, $daysRemaining, now());

        $newMonthlyPrice = $newUsersCount * $pricePerUser;

        try {
            // Создаем платеж через выбранный шлюз (по умолчанию robokassa)
            $gatewayName = config('services.payment.gateway', 'robokassa');
            $gateway = PaymentGatewayFactory::create($gatewayName);
            
            $payment = $gateway->createPayment([
                'subscription_id' => $subscription->id,
                'amount' => $proportionalPrice,
                'description' => "Апгрейд: +{$addedUsers} польз. на {$daysRemaining} дн.",
                'months' => 0, // Это апгрейд, не продление
                'user_limit' => $newUsersCount,
                'plan_id' => $subscription->subscription_plan_id,
            ]);

            $paymentUrl = $payment->provider_data['payment_url'];

            return [
                'success' => true,
                'message' => "Добавлено пользователей: +{$addedUsers}. Перенаправление на оплату.",
                'price' => $proportionalPrice,
                'payment_url' => $paymentUrl,
                'payment_id' => $payment->id,
            ];

        } catch (Exception $e) {
            return [
                'success' => false,
                'message' => 'Ошибка создания платежа: ' . $e->getMessage(),
            ];
        }
    }

    /**
     * Оплата подписок для всех отделов организации
     * 
     * @param int $organizationId ID организации
     * @param int $newLimit Новый лимит пользователей
     * @param int $newPlanId ID нового тарифного плана
     * @param int $months Количество месяцев
     * @return array Результат операции ['success' => bool, 'message' => string, 'count' => int]
     */
    public function payAllDepartments(int $organizationId, int $newLimit, int $newPlanId, int $months): array
    {
        $departments = Department::where('organization_id', $organizationId)
            ->where('is_active', true)
            ->get();

        $successCount = 0;
        $errors = [];

        foreach ($departments as $department) {
            $subscription = $department->getActiveSubscription();
            
            if (!$subscription) {
                $subscription = Subscription::create([
                    'department_id' => $department->id,
                    'organization_id' => $organizationId,
                    'subscription_plan_id' => $newPlanId,
                    'starts_at' => now(),
                    'ends_at' => now()->addMonths($months),
                    'trial_ends_at' => null,
                    'paid_users_limit' => $newLimit,
                    'monthly_price' => $newLimit * SubscriptionPlan::find($newPlanId)->price_per_user,
                    'auto_renew' => false,
                ]);
                $successCount++;
                continue;
            }

            $result = $this->pay($subscription, $newLimit, $newPlanId, $months);
            
            if ($result['success']) {
                $successCount++;
            } else {
                $errors[] = "{$department->name}: {$result['message']}";
            }
        }

        if ($successCount === 0) {
            return [
                'success' => false,
                'message' => 'Не удалось оплатить ни один отдел. ' . implode(' ', $errors),
                'count' => 0
            ];
        }

        $message = "Оплачено отделов: {$successCount}";
        if (count($errors) > 0) {
            $message .= '. Ошибки: ' . implode(' ', $errors);
        }

        return [
            'success' => true,
            'message' => $message,
            'count' => $successCount
        ];
    }
}


