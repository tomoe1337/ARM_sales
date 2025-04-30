<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Deal extends Model
{
    use HasFactory;

    protected $fillable = [
        'title',
        'description',
        'amount',
        'status',
        'closed_at',
        'user_id',
        'client_id',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'closed_at' => 'datetime',
    ];

    public function client()
    {
        return $this->belongsTo(Client::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function canView(User $user)
    {
        return $user->isHead() || $this->user_id === $user->id;
    }

    public function canEdit(User $user)
    {
        return $user->isHead() || $this->user_id === $user->id;
    }

    public function canDelete(User $user)
    {
        return $user->isHead() || $this->user_id === $user->id;
    }
}
