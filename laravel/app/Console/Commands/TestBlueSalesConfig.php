<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class TestBlueSalesConfig extends Command
{
    protected $signature = 'bluesales:test-config';
    protected $description = 'Test BlueSales configuration';

    public function handle()
    {
        $this->info('BlueSales Configuration:');
        $this->line('');
        $this->line('BLUESALES_SYNC_ENABLED: ' . (config('bluesales.sync_enabled') ? 'true' : 'false'));
        $this->line('BLUESALES_LOGIN: ' . (config('bluesales.login') ?: 'not set'));
        $this->line('BLUESALES_API_KEY: ' . (config('bluesales.api_key') ? '***' . substr(config('bluesales.api_key'), -4) : 'not set'));
        $this->line('');
        
        $shouldSync = config('bluesales.sync_enabled', false) 
            && config('bluesales.login') 
            && config('bluesales.api_key');
            
        $this->line('Should sync: ' . ($shouldSync ? 'YES' : 'NO'));
        
        return 0;
    }
}

