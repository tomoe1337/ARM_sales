<?php

namespace Database\Seeders;

use App\Models\SubscriptionPlan;
use Illuminate\Database\Seeder;

class SubscriptionPlanSeeder extends Seeder
{
    public function run(): void
    {
        SubscriptionPlan::firstOrCreate(
            ['slug' => 'standard2'],
            [
                'name' => 'Стандарт',
                'description' => 'Стандартный тарифный план',
                'price_per_user' => 450,
                'ai_analytics_enabled' => true,
                'crm_sync_enabled' => true,
                'is_active' => true,
            ]
        );
    }
}




