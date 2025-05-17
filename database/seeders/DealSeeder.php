<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class DealSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $deals = [];
        $startOfWeek = Carbon::now()->startOfWeek();
        $endOfWeek = Carbon::now()->endOfWeek();
        for ($i = 0; $i < 50; $i++) { // Создать 50 случайных сделок
            $randomDate = Carbon::createFromTimestamp(rand($startOfWeek->timestamp, $endOfWeek->timestamp));
            $deals[] = [
                'user_id' => rand(1, 6), // Случайный пользователь от 1 до 6
                'client_id' => rand(1, 18), // Случайный клиент от 1 до 18
                'title' => 'Сделка #' . ($i + 1),
                'amount' => rand(1000, 100000), // Сумма от 1000 до 100000
                'status' => ['new', 'lost', 'in_progress', 'won'][rand(0, 3)],
                'created_at' => $randomDate,
                'closed_at' => $randomDate->addDays(rand(0, 2)),
                'updated_at' => $randomDate,
                'description' => "some text"
            ];
        }

        // Вставляем данные в таблицу deals
        DB::table('deals')->insert($deals);
    }
}
