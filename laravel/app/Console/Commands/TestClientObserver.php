<?php

namespace App\Console\Commands;

use App\Models\Client;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Event;

class TestClientObserver extends Command
{
    protected $signature = 'test:client-observer';
    protected $description = 'Test if ClientObserver is working';

    public function handle()
    {
        $this->info('Checking Observer registration...');
        
        // Проверяем, зарегистрирован ли Observer
        $observers = Client::getObservableEvents();
        $this->info('Observable events: ' . implode(', ', $observers));
        
        // Проверяем listeners
        $listeners = Event::getListeners('eloquent.created: ' . Client::class);
        $this->info('Listeners count: ' . count($listeners));
        
        $this->info('Creating test client...');
        
        $client = Client::create([
            'name' => 'Test Observer Client ' . time(),
            'phone' => '79999999999',
            'user_id' => 1,
        ]);
        
        $this->info('Client created with ID: ' . $client->id);
        $this->info('Check logs for ClientObserver entries');
        
        return 0;
    }
}

