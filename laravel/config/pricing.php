<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Pricing Configuration
    |--------------------------------------------------------------------------
    |
    | Здесь настраиваются тарифы для отображения на лендинге и в системе.
    | Все цены указываются в рублях.
    |
    */

    // Цена подписки ARM Sales за сотрудника в месяц
    'monthly_price' => env('PRICING_MONTHLY_PRICE', 450),

    // Цена базового тарифа BlueSales (для сравнения)
    'bluesales_basic_price' => env('PRICING_BLUESALES_BASIC', 999),

    // Валюта
    'currency' => env('PRICING_CURRENCY', 'RUB'),
    'currency_symbol' => env('PRICING_CURRENCY_SYMBOL', '₽'),

];



