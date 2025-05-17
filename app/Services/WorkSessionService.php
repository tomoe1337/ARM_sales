<?php

namespace App\Services;

use App\Models\User;
use App\Models\WorkSession;

class WorkSessionService
{
    /**
     * Start a new work session for the given user.
     *
     * @param User $user
     * @return WorkSession
     */
    public function startSession(User $user): WorkSession
    {
        return WorkSession::create([
            'user_id' => $user->id,
            'start_time' => now(),
        ]);
    }

    /**
     * End the given work session.
     *
     * @param WorkSession $session
     * @return bool
     */
    public function endSession(WorkSession $session): bool
    {
        return $session->update([
            'end_time' => now(),
        ]);
    }

    /**
     * Get work session report data grouped by user.
     *
     * @return \Illuminate\Support\Collection
     */
    public function getReportData(?User $user = null)
    {
        $query = WorkSession::with('user')
            ->orderBy('start_time', 'desc');

        if ($user) {
            $query->where('user_id', $user->id);
        }

        return $query
            ->orderBy('start_time', 'desc')
            ->get()
            ->groupBy('user_id');
    }
}