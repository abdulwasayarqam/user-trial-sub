<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SubscriptionDetail extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'stripe_subscription_id',
        'stripe_subscription_schedule_id',
        'stripe_customer_id',
        'subscription_plan_price_id',
        'plan_amount',
        'plan_amount_currency',
        'plan_interval',
        'plan_interval_count',
        'created',
        'plan_period_start',
        'plan_period_end',
        'trial_end',
        'status',
        'cancel',
        'canceled_at'
    ];

    public function user()
    {
        return $this->hasOne(User::class,'id', 'user_id');
    }

}
