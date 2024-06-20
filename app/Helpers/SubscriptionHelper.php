<?php

namespace App\Helpers;
use App\Models\SubscriptionDetail;
use App\Models\SubscriptionPlan;
use App\Models\User;
use Illuminate\Support\Facades\Log;
use App\Models\PendingFee;

use Stripe\Stripe;
use Stripe\Subscription;

class SubscriptionHelper
{

    public static function start_monthly_trial_subscription($customer_id, $user_id, $subscriptionPlan)
    {
        try{

            $stripeData = null;
            $current_period_start = date('Y-m-d H:i:s');

            $Date = date('Y-m-d 23:59:59');

            $trialDays = strtotime($Date.'+'.$subscriptionPlan->trial_days.' days');

            $subscriptionDetailsData = [
                'user_id' => $user_id,
                'stripe_subscription_id' => NULL,
                'stripe_subscription_schedule_id' => "",
                'stripe_customer_id' => $customer_id,
                'subscription_plan_price_id' => $subscriptionPlan->stripe_price_id,
                'plan_amount' => $subscriptionPlan->amount,
                'plan_amount_currency' => 'usd',
                'plan_interval' => 'month',
                'plan_interval_count' => 1,
                'created' => date('Y-m-d H:i:s'),
                'plan_period_start' => $current_period_start,
                'plan_period_end' => date('Y-m-d H:i:s', $trialDays),
                'trial_end' => $trialDays,
                'status' => 'active',
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s')
            ];

            $stripeData = SubscriptionDetail::updateOrCreate(
                [
                    'user_id' => $user_id,
                    'stripe_customer_id' => $customer_id,
                    'subscription_plan_price_id' => $subscriptionPlan->stripe_price_ids
                ],
                $subscriptionDetailsData
            );
            User::where('id', $user_id)->update(['is_subscribed' => 1]);

            return $stripeData;

        }
        catch(\Exception $e){
            Log::info($e->getMessage());
            return null;
        }
    }

    public static function start_yearly_trial_subscription($customer_id, $user_id, $subscriptionPlan)
    {
        try{

            $stripeData = null;
            $current_period_start = date('Y-m-d H:i:s');

            $Date = date('Y-m-d 23:59:59');

            $trialDays = strtotime($Date.'+'.$subscriptionPlan->trial_days.' days');

            $subscriptionDetailsData = [
                'user_id' => $user_id,
                'stripe_subscription_id' => NULL,
                'stripe_subscription_schedule_id' => "",
                'stripe_customer_id' => $customer_id,
                'subscription_plan_price_id' => $subscriptionPlan->stripe_price_id,
                'plan_amount' => $subscriptionPlan->amount,
                'plan_amount_currency' => 'usd',
                'plan_interval' => 'year',
                'plan_interval_count' => 1,
                'created' => date('Y-m-d H:i:s'),
                'plan_period_start' => $current_period_start,
                'plan_period_end' => date('Y-m-d H:i:s', $trialDays),
                'trial_end' => $trialDays,
                'status' => 'active',
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s')
            ];

            $stripeData = SubscriptionDetail::updateOrCreate(
                [
                    'user_id' => $user_id,
                    'stripe_customer_id' => $customer_id,
                    'subscription_plan_price_id' => $subscriptionPlan->stripe_price_ids
                ],
                $subscriptionDetailsData
            );
            User::where('id', $user_id)->update(['is_subscribed' => 1]);

            return $stripeData;

        }
        catch(\Exception $e){
            Log::info($e->getMessage());
            return null;
        }
    }

    public static function start_lifetime_trial_subscription($customer_id, $user_id, $subscriptionPlan)
    {
        try{

            $stripeData = null;
            $current_period_start = date('Y-m-d H:i:s');

            $Date = date('Y-m-d 23:59:59');

            $trialDays = strtotime($Date.'+'.$subscriptionPlan->trial_days.' days');

            $subscriptionDetailsData = [
                'user_id' => $user_id,
                'stripe_subscription_id' => NULL,
                'stripe_subscription_schedule_id' => "",
                'stripe_customer_id' => $customer_id,
                'subscription_plan_price_id' => $subscriptionPlan->stripe_price_id,
                'plan_amount' => $subscriptionPlan->amount,
                'plan_amount_currency' => 'usd',
                'plan_interval' => 'lifetime',
                'plan_interval_count' => 1,
                'created' => date('Y-m-d H:i:s'),
                'plan_period_start' => $current_period_start,
                'plan_period_end' => date('Y-m-d H:i:s', $trialDays),
                'trial_end' => $trialDays,
                'status' => 'active',
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s')
            ];

            $stripeData = SubscriptionDetail::updateOrCreate(
                [
                    'user_id' => $user_id,
                    'stripe_customer_id' => $customer_id,
                    'subscription_plan_price_id' => $subscriptionPlan->stripe_price_ids
                ],
                $subscriptionDetailsData
            );
            User::where('id', $user_id)->update(['is_subscribed' => 1]);

            return $stripeData;

        }
        catch(\Exception $e){
            Log::info($e->getMessage());
            return null;
        }
    }

    public static function capture_monthly_pending_fees($customer_id, $user_id, $user_name, $subscriptionPlan, $stripe)
    {

        $totalAmount = $subscriptionPlan->amount;

        $daysInMonth = date('t');
        $currentDay = date('j');
        $amountForRestDays = ceil(($daysInMonth - $currentDay) * ($totalAmount / $daysInMonth));

        $stripeChargeData = $stripe->charges->create([
            'amount' => $amountForRestDays*100,
            'currency' => 'usd',
            'customer' => $customer_id,
            'description' => 'Monthly Pending Fees',
            'shipping' => [
                'name' => $user_name,
                'address' => [
                    'line1' => '123 Main Sta',
                    'line2' => 'Apt 1',
                    'city' => 'Anytown',
                    'state' => 'NY',
                    'postal_code' => '12345',
                    'country' => 'US',
                ]
            ]
        ]);

        if(!empty($stripeChargeData)){
            $stripeCharge = $stripeChargeData->jsonSerialize();

            $chargeId = $stripeCharge['id'];
            $cusId = $stripeCharge['customer'];

            $pendingFeeData = [
                'user_id' => $user_id,
                'charge_id' => $chargeId,
                'customer_id' => $cusId,
                'amount' => $amountForRestDays,
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s')
            ];

            PendingFee::insert($pendingFeeData);

        }

    }

    public static function start_monthly_subscription($customer_id, $user_id, $subscriptionPlan, $stripe)
    {
        try{

            $stripeData = null;

            $millisecondsDate = strtotime(date('Y-m-').'01');

            $current_period_start = date("Y-m-d",strtotime("+1 month", $millisecondsDate)).' 00:00:00';
            $current_period_end = date("Y-m-t",strtotime("+1 month")).' 23:59:59';

            $stripeData = $stripe->subscriptions->create([
                'customer' => $customer_id,
                'items' => [
                    ['price' => $subscriptionPlan->stripe_price_id]
                ],
                'billing_cycle_anchor' => strtotime($current_period_start),
                'proration_behavior' => 'none'
            ]);

            $stripeData = $stripeData->jsonSerialize();

            if(!empty($stripeData)){

                $subscriptionId = $stripeData['id'];
                $customerId = $stripeData['customer'];

                if(!empty($stripeData['items'])){
                    $planId = $stripeData['items']['data'][0]['plan']['id'];
                }
                else{
                    $planId = $stripeData['plan']['id'];
                }

                $priceData = $stripe->plans->retrieve(
                    $planId,
                    []
                );

                $planAmount = ($priceData->amount/100);
                $planCurreny = $priceData->currency;
                $planInterval = $priceData->interval;
                $planIntervalCount = $priceData->interval_count;

                $created = date('Y-m-d H:i:s', $stripeData['created']);

                $subscriptionDetailsData = [
                    'user_id' => $user_id,
                    'stripe_subscription_id' => $subscriptionId,
                    'stripe_subscription_schedule_id' => "",
                    'stripe_customer_id' => $customerId,
                    'subscription_plan_price_id' => $planId,
                    'plan_amount' => $planAmount,
                    'plan_amount_currency' => $planCurreny,
                    'plan_interval' => $planInterval,
                    'plan_interval_count' => $planIntervalCount,
                    'created' => $created,
                    'plan_period_start' => $current_period_start,
                    'plan_period_end' => $current_period_end,
                    'status' => 'active',
                    'created_at' => date('Y-m-d H:i:s'),
                    'updated_at' => date('Y-m-d H:i:s')
                ];

                $stripeData = SubscriptionDetail::insert( $subscriptionDetailsData );
                User::where('id', $user_id)->update(['is_subscribed' => 1]);

            }

            return $stripeData;

        }
        catch(\Exception $e){
            Log::info($e->getMessage());
            return null;
        }
    }

    public static function capture_yearly_pending_fees($customer_id, $user_id, $user_name, $subscriptionPlan, $stripe)
    {

        $totalAmount = $subscriptionPlan->amount;

        $monthsInYear = 12;
        $currentMonth = date('m')-1;
        $amountForRestMonths = ceil(($monthsInYear - $currentMonth) * ($totalAmount / $monthsInYear));

        $stripeChargeData = $stripe->charges->create([
            'amount' => $amountForRestMonths*100,
            'currency' => 'usd',
            'customer' => $customer_id,
            'description' => 'Monthly Pending Fees',
            'shipping' => [
                'name' => $user_name,
                'address' => [
                    'line1' => '123 Main Sta',
                    'line2' => 'Apt 1',
                    'city' => 'Anytown',
                    'state' => 'NY',
                    'postal_code' => '12345',
                    'country' => 'US',
                ]
            ]
        ]);

        if(!empty($stripeChargeData)){
            $stripeCharge = $stripeChargeData->jsonSerialize();

            $chargeId = $stripeCharge['id'];
            $cusId = $stripeCharge['customer'];

            $pendingFeeData = [
                'user_id' => $user_id,
                'charge_id' => $chargeId,
                'customer_id' => $cusId,
                'amount' => $amountForRestMonths,
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s')
            ];

            PendingFee::insert($pendingFeeData);

        }

    }

    public static function start_yearly_subscription($customer_id, $user_id, $subscriptionPlan, $stripe)
    {
        try{

            $stripeData = null;

            $current_period_start = date('Y-', strtotime('+1 year')).'01-01 00:00:00';
            $current_period_end = date('Y-', strtotime('+1 year')).'12-31 23:59:59';

            $stripeData = $stripe->subscriptions->create([
                'customer' => $customer_id,
                'items' => [
                    ['price' => $subscriptionPlan->stripe_price_id]
                ],
                'billing_cycle_anchor' => strtotime($current_period_start),
                'proration_behavior' => 'none'
            ]);

            $stripeData = $stripeData->jsonSerialize();

            if(!empty($stripeData)){

                $subscriptionId = $stripeData['id'];
                $customerId = $stripeData['customer'];

                if(!empty($stripeData['items'])){
                    $planId = $stripeData['items']['data'][0]['plan']['id'];
                }
                else{
                    $planId = $stripeData['plan']['id'];
                }

                $priceData = $stripe->plans->retrieve(
                    $planId,
                    []
                );

                $planAmount = ($priceData->amount/100);
                $planCurreny = $priceData->currency;
                $planInterval = $priceData->interval;
                $planIntervalCount = $priceData->interval_count;

                $created = date('Y-m-d H:i:s', $stripeData['created']);

                $subscriptionDetailsData = [
                    'user_id' => $user_id,
                    'stripe_subscription_id' => $subscriptionId,
                    'stripe_subscription_schedule_id' => "",
                    'stripe_customer_id' => $customerId,
                    'subscription_plan_price_id' => $planId,
                    'plan_amount' => $planAmount,
                    'plan_amount_currency' => $planCurreny,
                    'plan_interval' => $planInterval,
                    'plan_interval_count' => $planIntervalCount,
                    'created' => $created,
                    'plan_period_start' => $current_period_start,
                    'plan_period_end' => $current_period_end,
                    'status' => 'active',
                    'created_at' => date('Y-m-d H:i:s'),
                    'updated_at' => date('Y-m-d H:i:s')
                ];

                $stripeData = SubscriptionDetail::insert( $subscriptionDetailsData );
                User::where('id', $user_id)->update(['is_subscribed' => 1]);

            }

            return $stripeData;

        }
        catch(\Exception $e){
            Log::info($e->getMessage());
            return null;
        }
    }

    public static function start_lifetime_subscription($customer_id, $user_id, $user_name, $subscriptionPlan, $stripe)
    {
        try{

            $stripeData = null;
            $current_period_start = date('Y-m-d H:i:s');
            $current_period_end = '2099-'.date('m-d').' 23:59:59';

            $stripeChargeData = $stripe->charges->create([
                'amount' => $subscriptionPlan->amount*100,
                'currency' => 'usd',
                'customer' => $customer_id,
                'description' => 'Lifetime Subscription Buy',
                'shipping' => [
                    'name' => $user_name,
                    'address' => [
                        'line1' => '123 Main Sta',
                        'line2' => 'Apt 1',
                        'city' => 'Anytown',
                        'state' => 'NY',
                        'postal_code' => '12345',
                        'country' => 'US',
                    ]
                ]
            ]);

            if(!empty($stripeChargeData)){
                $stripeCharge = $stripeChargeData->jsonSerialize();

                $chargeId = $stripeCharge['id'];
                $cusId = $stripeCharge['customer'];
                $planCurreny = $stripeCharge['currency'];
                $created = date('Y-m-d H:i:s', $stripeCharge['created']);

                $subscriptionDetailsData = [
                    'user_id' => $user_id,
                    'stripe_subscription_id' => $chargeId,
                    'stripe_subscription_schedule_id' => "",
                    'stripe_customer_id' => $cusId,
                    'subscription_plan_price_id' => $subscriptionPlan->stripe_price_id,
                    'plan_amount' => $subscriptionPlan->amount,
                    'plan_amount_currency' => $planCurreny,
                    'plan_interval' => 'lifetime',
                    'plan_interval_count' => 1,
                    'created' => $created,
                    'plan_period_start' => $current_period_start,
                    'plan_period_end' => $current_period_end,
                    'status' => 'active',
                    'created_at' => date('Y-m-d H:i:s'),
                    'updated_at' => date('Y-m-d H:i:s')
                ];

                $stripeData = SubscriptionDetail::insert( $subscriptionDetailsData );
                User::where('id', $user_id)->update(['is_subscribed' => 1]);

            }

            return $stripeData;

        }
        catch(\Exception $e){
            Log::info($e->getMessage());
            return null;
        }
    }

    public static function cancel_current_subscription($user_id, $subscriptionDetail)
    {
        try{

            $secretKey = env('STRIPE_SECRET_KEY');
            Stripe::setApiKey($secretKey);

            if($subscriptionDetail->stripe_subscription_id != null && $subscriptionDetail->stripe_subscription_id != ''){
                $subscription = Subscription::retrieve($subscriptionDetail->stripe_subscription_id);
                Log::info($subscription);

                //cancel the stripe subscription
                $subscription->cancel();
            }

            SubscriptionDetail::where('id', $subscriptionDetail->id)->update([
                'status' => 'cancelled',
                'cancel' => 1,
                'canceled_at' => date('Y-m-d H:i:s')
            ]);

            User::where('id', $user_id)->update([ 'is_subscribed' => 0 ]);

        }
        catch(\Exception $e){
            Log::info($e->getMessage());
        }
    }

    public static function getCurrentSubscription()
    {
        $currentSubscription = SubscriptionDetail::where([
            'user_id' => auth()->user()->id,
            'status' => 'active',
            'cancel' => 0
        ])->orderBy('id', 'desc')->first();

        return $currentSubscription;

    }

    //below are the renew subscription methods
    public static function renew_monthly_subscription($subscriptionDetail, $user_id, $subscriptionPlan, $stripe)
    {
        try{

            $stripeData = null;

            $millisecondsDate = strtotime(date('Y-m-').'01');

            $current_period_start = date("Y-m-d",strtotime("+1 month", $millisecondsDate)).' 00:00:00';
            $current_period_end = date("Y-m-t",strtotime("+1 month")).' 23:59:59';

            $stripeData = $stripe->subscriptions->create([
                'customer' => $subscriptionDetail->stripe_customer_id,
                'items' => [
                    ['price' => $subscriptionPlan->stripe_price_id]
                ],
                'billing_cycle_anchor' => strtotime($current_period_start),
                'proration_behavior' => 'none'
            ]);

            $stripeData = $stripeData->jsonSerialize();

            if(!empty($stripeData)){

                $subscriptionId = $stripeData['id'];
                $customerId = $stripeData['customer'];

                if(!empty($stripeData['items'])){
                    $planId = $stripeData['items']['data'][0]['plan']['id'];
                }
                else{
                    $planId = $stripeData['plan']['id'];
                }

                $priceData = $stripe->plans->retrieve(
                    $planId,
                    []
                );

                $planAmount = ($priceData->amount/100);
                $planCurreny = $priceData->currency;
                $planInterval = $priceData->interval;
                $planIntervalCount = $priceData->interval_count;

                $created = date('Y-m-d H:i:s', $stripeData['created']);

                $subscriptionDetailsData = [
                    'user_id' => $user_id,
                    'stripe_subscription_id' => $subscriptionId,
                    'stripe_subscription_schedule_id' => "",
                    'stripe_customer_id' => $customerId,
                    'subscription_plan_price_id' => $planId,
                    'plan_amount' => $planAmount,
                    'plan_amount_currency' => $planCurreny,
                    'plan_interval' => $planInterval,
                    'plan_interval_count' => $planIntervalCount,
                    'created' => $created,
                    'plan_period_start' => $current_period_start,
                    'plan_period_end' => $current_period_end,
                    'trial_end' => NULL,
                    'status' => 'active',
                    'created_at' => date('Y-m-d H:i:s'),
                    'updated_at' => date('Y-m-d H:i:s')
                ];

                $stripeData = SubscriptionDetail::where('id', $subscriptionDetail->id)->update( $subscriptionDetailsData );
                User::where('id', $user_id)->update(['is_subscribed' => 1]);

            }

            return $stripeData;

        }
        catch(\Exception $e){
            Log::info($e->getMessage());
            return null;
        }
    }

    public static function renew_yearly_subscription($subscriptionDetail, $user_id, $subscriptionPlan, $stripe)
    {
        try{

            $stripeData = null;

            $current_period_start = date('Y-', strtotime('+1 year')).'01-01 00:00:00';
            $current_period_end = date('Y-', strtotime('+1 year')).'12-31 23:59:59';

            $stripeData = $stripe->subscriptions->create([
                'customer' => $subscriptionDetail->stripe_customer_id,
                'items' => [
                    ['price' => $subscriptionPlan->stripe_price_id]
                ],
                'billing_cycle_anchor' => strtotime($current_period_start),
                'proration_behavior' => 'none'
            ]);

            $stripeData = $stripeData->jsonSerialize();

            if(!empty($stripeData)){

                $subscriptionId = $stripeData['id'];
                $customerId = $stripeData['customer'];

                if(!empty($stripeData['items'])){
                    $planId = $stripeData['items']['data'][0]['plan']['id'];
                }
                else{
                    $planId = $stripeData['plan']['id'];
                }

                $priceData = $stripe->plans->retrieve(
                    $planId,
                    []
                );

                $planAmount = ($priceData->amount/100);
                $planCurreny = $priceData->currency;
                $planInterval = $priceData->interval;
                $planIntervalCount = $priceData->interval_count;

                $created = date('Y-m-d H:i:s', $stripeData['created']);

                $subscriptionDetailsData = [
                    'user_id' => $user_id,
                    'stripe_subscription_id' => $subscriptionId,
                    'stripe_subscription_schedule_id' => "",
                    'stripe_customer_id' => $customerId,
                    'subscription_plan_price_id' => $planId,
                    'plan_amount' => $planAmount,
                    'plan_amount_currency' => $planCurreny,
                    'plan_interval' => $planInterval,
                    'plan_interval_count' => $planIntervalCount,
                    'created' => $created,
                    'plan_period_start' => $current_period_start,
                    'plan_period_end' => $current_period_end,
                    'trial_end' => NULL,
                    'status' => 'active',
                    'created_at' => date('Y-m-d H:i:s'),
                    'updated_at' => date('Y-m-d H:i:s')
                ];

                $stripeData = SubscriptionDetail::where('id', $subscriptionDetail->id)->update( $subscriptionDetailsData );
                User::where('id', $user_id)->update(['is_subscribed' => 1]);

            }

            return $stripeData;

        }
        catch(\Exception $e){
            Log::info($e->getMessage());
            return null;
        }
    }

    public static function renew_lifetime_subscription($subscriptionDetail, $user_id, $user_name, $subscriptionPlan, $stripe)
    {
        try{

            $stripeData = null;
            $current_period_start = date('Y-m-d H:i:s');
            $current_period_end = '2099-'.date('m-d').' 23:59:59';

            $stripeChargeData = $stripe->charges->create([
                'amount' => $subscriptionPlan->amount*100,
                'currency' => 'usd',
                'customer' => $subscriptionDetail->stripe_customer_id,
                'description' => 'Lifetime Subscription Buy',
                'shipping' => [
                    'name' => $user_name,
                    'address' => [
                        'line1' => '123 Main Sta',
                        'line2' => 'Apt 1',
                        'city' => 'Anytown',
                        'state' => 'NY',
                        'postal_code' => '12345',
                        'country' => 'US',
                    ]
                ]
            ]);

            if(!empty($stripeChargeData)){
                $stripeCharge = $stripeChargeData->jsonSerialize();

                $chargeId = $stripeCharge['id'];
                $cusId = $stripeCharge['customer'];
                $planCurreny = $stripeCharge['currency'];
                $created = date('Y-m-d H:i:s', $stripeCharge['created']);

                $subscriptionDetailsData = [
                    'user_id' => $user_id,
                    'stripe_subscription_id' => $chargeId,
                    'stripe_subscription_schedule_id' => "",
                    'stripe_customer_id' => $cusId,
                    'subscription_plan_price_id' => $subscriptionPlan->stripe_price_id,
                    'plan_amount' => $subscriptionPlan->amount,
                    'plan_amount_currency' => $planCurreny,
                    'plan_interval' => 'lifetime',
                    'plan_interval_count' => 1,
                    'created' => $created,
                    'plan_period_start' => $current_period_start,
                    'plan_period_end' => $current_period_end,
                    'trial_end' => NULL,
                    'status' => 'active',
                    'created_at' => date('Y-m-d H:i:s'),
                    'updated_at' => date('Y-m-d H:i:s')
                ];

                $stripeData = SubscriptionDetail::where('id', $subscriptionDetail->id)->update( $subscriptionDetailsData );
                User::where('id', $user_id)->update(['is_subscribed' => 1]);

            }

            return $stripeData;

        }
        catch(\Exception $e){
            Log::info($e->getMessage());
            return null;
        }
    }

}
