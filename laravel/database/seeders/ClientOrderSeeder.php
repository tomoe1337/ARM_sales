<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use App\Models\User;
use App\Models\Client;
use App\Models\Order;
use Faker\Factory as Faker;

class ClientOrderSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $faker = Faker::create('ru_RU');
        
        // Получаем всех пользователей с ролью manager или head
        $users = User::role(['manager', 'head'])->get();
        
        if ($users->isEmpty()) {
            $this->command->warn('Нет пользователей с ролью manager или head. Создайте пользователей сначала.');
            return;
        }

        // Массивы для рандомных значений
        $successStatuses = ['closed_won', 'delivered', 'completed']; // Статусы клиентов с заказами
        $failedStatuses = ['new', 'contacted', 'qualified', 'proposal', 'negotiation', 'closed_lost']; // Статусы лидов без заказов
        $sources = ['website', 'social_media', 'referral', 'cold_call', 'email_campaign', 'exhibition', 'partner'];
        $salesChannels = ['online', 'retail', 'wholesale', 'direct', 'partner'];
        $orderStatuses = ['new', 'reserve', 'preorder', 'shipped', 'delivered', 'cancelled'];
        $deliveryServices = [1, 2, 3, 4, 5, 6]; // ID сервисов доставки
        $deliveryServiceNames = ['Почта России', 'СДЭК', 'DPD', 'Boxberry', 'PickPoint', 'Самовывоз'];
        $cities = ['Москва', 'Санкт-Петербург', 'Новосибирск', 'Екатеринбург', 'Казань', 'Нижний Новгород', 'Челябинск', 'Самара', 'Омск', 'Ростов-на-Дону'];
        $countries = ['Россия', 'Беларусь', 'Казахстан', 'Украина'];

        $clients = [];
        $orders = [];
        
        // Сначала определяем количество заказов для каждого лида
        $clientOrderCounts = [];
        for ($i = 1; $i <= 80; $i++) {
            // Реалистичная конверсия лидов в заказы (~10-12%)
            $rand = $faker->randomFloat(3, 0, 1);
            if ($rand < 0.88) {
                $orderCount = 0; // Большинство лидов не покупает
            } elseif ($rand < 0.96) {
                $orderCount = 1; // Небольшая часть покупает один раз
            } elseif ($rand < 0.988) {
                $orderCount = 2; // Еще меньше покупает повторно
            } elseif ($rand < 0.996) {
                $orderCount = 3; // Очень редко - лояльные клиенты
            } else {
                $orderCount = $faker->numberBetween(4, 6); // Единицы VIP клиентов
            }
            $clientOrderCounts[] = $orderCount;
        }

        // Создаем лидов (со статусами в соответствии с наличием заказов)
        for ($i = 0; $i < 80; $i++) {
            $user = $users->random();
            $firstName = $faker->firstName();
            $lastName = $faker->lastName();
            $fullName = $lastName . ' ' . $firstName;
            
            // Даты контактов (в пределах текущей недели для попадания в AI анализ)
            $weekStart = now()->startOfWeek();
            $weekEnd = now()->endOfWeek();
            $firstContactDate = $faker->dateTimeBetween($weekStart, $weekEnd);
            $lastContactDate = $faker->dateTimeBetween($firstContactDate, $weekEnd); // Последний контакт после первого
            $nextContactDate = $faker->optional(0.7)->dateTimeBetween($weekEnd, $weekEnd->copy()->addWeek());
            
            // Определяем статус в зависимости от наличия заказов
            $orderCount = $clientOrderCounts[$i];
            if ($orderCount > 0) {
                // Клиенты с заказами получают успешные статусы
                $crmStatus = $faker->randomElement($successStatuses);
            } else {
                // Лиды без заказов получают неуспешные статусы
                $crmStatus = $faker->randomElement($failedStatuses);
            }
            
            $client = [
                'name' => $fullName,
                'full_name' => $fullName,
                'phone' => '+7' . $faker->numerify('##########'),
                'email' => $faker->unique()->safeEmail(),
                'address' => $faker->address(),
                'description' => $faker->optional(0.6)->paragraph(),
                'user_id' => $user->id,
                
                // BlueSales поля
                'bluesales_id' => $faker->unique()->numberBetween(1000, 99999),
                'country' => $faker->randomElement($countries),
                'city' => $faker->randomElement($cities),
                'birth_date' => $faker->optional(0.4)->dateTimeBetween('-80 years', '-18 years'),
                'gender' => $faker->optional(0.5)->randomElement(['male', 'female']),
                'vk_id' => $faker->optional(0.3)->numerify('vk_#########'),
                'ok_id' => $faker->optional(0.2)->numerify('ok_#########'),
                'crm_status' => $crmStatus,
                'first_contact_date' => $firstContactDate,
                'next_contact_date' => $nextContactDate,
                'last_contact_date' => $lastContactDate,
                'source' => $faker->randomElement($sources),
                'sales_channel' => $faker->randomElement($salesChannels),
                'tags' => $faker->optional(0.5)->words(3, true),
                'notes' => $faker->optional(0.4)->sentence(),
                'additional_contacts' => $faker->optional(0.3)->phoneNumber(),
                'bluesales_last_sync' => $faker->dateTimeBetween($weekStart, $weekEnd),
                'created_at' => $firstContactDate,
                'updated_at' => $faker->dateTimeBetween($firstContactDate, $weekEnd),
            ];
            
            $clients[] = $client;
        }

        // Вставляем лидов в базу
        DB::table('clients')->insert($clients);
        
        // Получаем ID созданных лидов
        $clientIds = DB::table('clients')->orderBy('id', 'desc')->limit(80)->pluck('id');
        
        // Создаем заказы только для клиентов с успешными статусами
        foreach ($clientIds as $index => $clientId) {
            $orderCount = $clientOrderCounts[$index];
            
            if ($orderCount === 0) {
                continue; // Пропускаем лидов без заказов
            }
            
            $client = DB::table('clients')->find($clientId);
            
            for ($j = 0; $j < $orderCount; $j++) {
                $orderDate = $faker->dateTimeBetween($client->first_contact_date, $weekEnd); // Заказы только в пределах текущей недели
                // Более разнообразные суммы заказов (от маленьких до крупных)
                $totalAmount = $faker->biasedNumberBetween(300, 150000, function($x) {
                    // Больше маленьких заказов, меньше крупных
                    return pow(1 - $x, 2);
                });
                $discount = $faker->randomFloat(4, 0, 0.3); // скидка до 30%
                $moneyDiscount = $discount > 0 ? $totalAmount * $discount : 0;
                $deliveryCost = $faker->randomFloat(2, 0, 2000);
                $prepay = $faker->optional(0.6)->randomFloat(2, 0, $totalAmount * 0.5) ?? 0; // предоплата до 50%
                
                $order = [
                    'bluesales_id' => $faker->unique()->numberBetween(10000, 999999),
                    'client_id' => $clientId,
                    'user_id' => $client->user_id,
                    'deal_id' => null, // Новая архитектура - заказы не связаны со сделками
                    // Статус не влияет на факт продажи - любой заказ = оплаченная продажа
                    'status' => $faker->randomElement($orderStatuses),
                    'internal_number' => 'ORD-' . str_pad($faker->unique()->numberBetween(1, 99999), 5, '0', STR_PAD_LEFT),
                    'external_number' => $faker->optional(0.7)->numerify('EXT-########'),
                    'order_date' => $orderDate,
                    'total_amount' => $totalAmount,
                    'discount' => $discount,
                    'money_discount' => $moneyDiscount,
                    'delivery_cost' => $deliveryCost,
                    'prepay' => $prepay,
                    'tracking_number' => $faker->optional(0.5)->numerify('##############'),
                    'delivery_service' => $faker->randomElement($deliveryServices),
                    'delivery_info' => $faker->optional(0.6)->sentence(),
                    'customer_comments' => $faker->optional(0.3)->sentence(),
                    'internal_comments' => $faker->optional(0.4)->sentence(),
                    'bluesales_last_sync' => $faker->dateTimeBetween($weekStart, $weekEnd),
                    'created_at' => $orderDate,
                    'updated_at' => $faker->dateTimeBetween($orderDate, $weekEnd),
                ];
                
                $orders[] = $order;
            }
        }

        // Вставляем заказы в базу
        if (!empty($orders)) {
            DB::table('orders')->insert($orders);
        }

        $this->command->info('Создано лидов: ' . count($clients));
        $this->command->info('Создано заказов: ' . count($orders));
        $this->command->info('Конверсия лид -> заказ: ' . round((count($orders) / count($clients)) * 100, 1) . '%');
    }
}