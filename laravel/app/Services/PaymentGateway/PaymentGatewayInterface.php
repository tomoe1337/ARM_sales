<?php

namespace App\Services\PaymentGateway;

use App\Models\Payment;

interface PaymentGatewayInterface
{
    /**
     * Создает новый платеж в платежной системе
     *
     * @param array{
     *     subscription_id: int,
     *     amount: float,
     *     description: string,
     *     months: int,
     *     user_limit: int
     * } $data Данные для создания платежа
     * @return \App\Models\Payment Результат создания платежа
     */
    public function createPayment(array $data): Payment;

    /**
     * Обрабатывает webhook от платежной системы
     *
     * @param array $data Данные вебхука
     * @return \App\Models\Payment Обновленный платеж
     */
    public function handleCallback(array $data): Payment;

    /**
     * Проверяет, работает ли шлюз в тестовом режиме
     *
     * @return bool true если шлюз работает в тестовом режиме, false в противном случае
     */
    public function isSandboxMode(): bool;
}

