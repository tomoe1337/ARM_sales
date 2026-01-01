<?php

namespace App\Services\PaymentGateway;

use Illuminate\Support\Facades\Log;

abstract class AbstractGateway implements PaymentGatewayInterface
{
    protected bool $sandboxMode;

    public function __construct()
    {
        $this->sandboxMode = config('services.payment.mode') === 'test';

        if ($this->sandboxMode) {
            Log::info(
                sprintf(
                    '%s API работает в тестовом режиме (PAYMENT_MODE=%s)',
                    $this->getGatewayName(),
                    config('services.payment.mode')
                )
            );
        }
    }

    public function isSandboxMode(): bool
    {
        return $this->sandboxMode;
    }

    abstract protected function getGatewayName(): string;
}

