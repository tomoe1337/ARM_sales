<?php

namespace Tests\Feature;

use App\Models\Client;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use App\Models\User;
use App\Models\Deal;
use App\Services\DealService;
use Illuminate\Support\Facades\DB;

class DealServiceTest extends TestCase
{
    use RefreshDatabase;

    protected $dealService;

    protected function setUp(): void
    {
        parent::setUp();
        $this->dealService = new DealService();
    }

    /** @test */

    /** @test */
    public function it_can_create_a_deal()
    {
        DB::connection()->getPdo(); // Убедиться, что БД доступна

        $user = User::factory()->create();

        $client = Client::factory()->create();


        $data = [
            'title' => 'Test Deal',
            'amount' => 1000,
            'status' => 'won',
            'user_id' => $user->id,
            'description' => 'some text',
            'client_id' => $client->id
        ];

        $deal = $this->dealService->createDeal($data);

        $this->assertInstanceOf(Deal::class, $deal);
        $this->assertDatabaseHas('deals', $data);
    }

    /** @test */
    public function it_can_update_a_deal()
    {
        $user = User::factory()->create();
        $deal = Deal::factory()->create([
            'user_id' => $user->id,
            'title' => 'Old Title',
            'amount' => 500,
            'status' => 'won',
            'description' => 'some text'
        ]);

        $updateData = [
            'title' => 'New Title',
            'amount' => 1000,
        ];

        $result = $this->dealService->updateDeal($deal, $updateData);

        $this->assertTrue($result);
        $this->assertDatabaseHas('deals', $updateData + ['id' => $deal->id]);
    }

    /** @test */
    public function it_can_delete_a_deal()
    {
        $user = User::factory()->create();
        $deal = Deal::factory()->create(['user_id' => $user->id]);

        $result = $this->dealService->deleteDeal($deal);

        $this->assertTrue($result);
        $this->assertDatabaseMissing('deals', ['id' => $deal->id]);
    }

    /** @test */
    public function it_gets_today_revenue_for_user()
    {
        $user = User::factory()->create();

        // Today's deal
        Deal::factory()->create([
            'user_id' => $user->id,
            'status' => 'won',
            'amount' => 200,
            'closed_at' => now(),
        ]);

        // Yesterday's deal (should not be counted)
        Deal::factory()->create([
            'user_id' => $user->id,
            'status' => 'won',
            'amount' => 300,
            'closed_at' => now()->subDay(),
        ]);

        $report = $this->dealService->getDayReportData($user);

        $this->assertEquals(200, $report['todayRevenue']);
    }

    /** @test */
    public function it_gets_monthly_revenue_for_user()
    {
        $user = User::factory()->create();

        // Deal in current month
        Deal::factory()->create([
            'user_id' => $user->id,
            'status' => 'won',
            'amount' => 500,
            'closed_at' => now(),
        ]);

        // Deal from previous month
        Deal::factory()->create([
            'user_id' => $user->id,
            'status' => 'won',
            'amount' => 600,
            'closed_at' => now()->subMonth(),
        ]);

        $report = $this->dealService->getMonthReportData($user);

        $this->assertEquals(500, $report['monthlyRevenue']);
    }

    /** @test */
    public function it_returns_empty_array_for_time_report_data()
    {
        $user = User::factory()->create();

        $report = $this->dealService->getTimeReportData($user);

        $this->assertIsArray($report);
        $this->assertEmpty($report);
    }
}
