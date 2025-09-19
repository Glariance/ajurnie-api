<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */

    use HasApiTokens, HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */

    public const ROLE_ADMIN = 'admin';
    public const ROLE_USER = 'user';

    protected $fillable = [
        'fullname',
        'email',
        'password',
        'role',
        'interval',
        'type',
        'is_paid',

        // Profile info
        'phone',
        'dob',
        'gender',
        'address1',
        'address2',
        'city',
        'state',
        'zip',
        'country',
        'bio',
        'avatar',

        // Stripe customer identifier (keep only this one!)
        'stripe_customer_id',

        // Auth/session
        'remember_token',
    ];


    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'dob' => 'date:Y-m-d',
        ];
    }


    public function subscriptions()
    {
        return $this->hasMany(\App\Models\Subscription::class);
    }
    public function currentSubscription()
    {
        return $this->hasOne(\App\Models\Subscription::class)->latestOfMany();
    }

    public function paymentMethods()
    {
        return $this->hasMany(PaymentMethod::class);
    }
}
