<?php

namespace App\Observers;

use App\Models\Deal;

class DealObserver
{
    /**
     * Handle the Deal "created" event.
     */
    public function created(Deal $deal): void
    {
        //
    }

    /**
     * Handle the Deal "updated" event.
     */
    public function updated(Deal $deal): void
    {
        //
    }

    /**
     * Handle the Deal "deleted" event.
     */
    public function deleted(Deal $deal): void
    {
        //
    }

    /**
     * Handle the Deal "restored" event.
     */
    public function restored(Deal $deal): void
    {
        //
    }

    /**
     * Handle the Deal "force deleted" event.
     */
    public function forceDeleted(Deal $deal): void
    {
        //
    }

    public function updating(Deal $deal)
    {
        \Log::info('DealObserver updating called', [
            'deal_id' => $deal->id,
            'status' => $deal->status,
            'is_dirty_status' => $deal->isDirty('status'),
            'closed_at' => $deal->closed_at
        ]);

        // Если статус меняется на 'won' и closed_at не установлен
        if ($deal->isDirty('status') && $deal->status === 'won' && !$deal->closed_at) {
            $deal->closed_at = now();
            \Log::info('Setting closed_at to now', ['deal_id' => $deal->id]);
        }
    }
}
