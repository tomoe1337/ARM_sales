<?php

namespace App\Services\PaymentGateway;

use InvalidArgumentException;

class PaymentGatewayFactory
{
    private const AVAILABLE_GATEWAYS = [
        'robokassa' => RobokassaGateway::class,
        'yookassa' => YookassaGateway::class,
    ];

    public static function create(string $gatewayName): PaymentGatewayInterface
    {
        if (!isset(self::AVAILABLE_GATEWAYS[$gatewayName])) {
            throw new InvalidArgumentException("Платежная система {$gatewayName} не поддерживается");
        }

        $gatewayClass = self::AVAILABLE_GATEWAYS[$gatewayName];
        return app($gatewayClass);
    }
}

