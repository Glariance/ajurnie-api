<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Subscription extends Model
{

    protected $fillable = [
        'user_id',
        'stripe_subscription_id',
        'price_id',
        'plan',
        'interval',
        'status',
        'trial_ends_at',
        'current_period_end',
        'canceled_at',
        'membership_type'
    ];

    protected $casts = [
        'trial_ends_at' => 'datetime',
        'current_period_end' => 'datetime',
        'canceled_at' => 'datetime',
    ];


    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
