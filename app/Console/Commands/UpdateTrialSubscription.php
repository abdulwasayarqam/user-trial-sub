<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\SubscriptionPlan;
use App\Models\SubscriptionDetail;
use Stripe\Stripe;
use Stripe\StripeClient;

use App\Helpers\SubscriptionHelper;

class UpdateTrialSubscription extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:update-trial-subscription';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Update Trial user Subscription into real subscription';

    /**
     * Execute the console command.
     */
    protected $STRIPE_SECRET_KEY;
    public function __construct()
    {
        $this->STRIPE_SECRET_KEY = env('STRIPE_SECRET_KEY');

        parent::__construct();
    }

    public function handle()
    {
        //
        $secretKey = $this->STRIPE_SECRET_KEY;
        Stripe::setApiKey($secretKey);
        $stripe = new StripeClient(
            $secretKey
        );

        $subscriptionDetails = SubscriptionDetail::with('user')->where(['status' => 'active','cancel' => 0])
        ->where('plan_period_end', '<', date('Y-m-d H:i:s'))->whereNotNull('trial_end')
        ->orderBy('id','desc')->get();

        if(count($subscriptionDetails) > 0){
            foreach($subscriptionDetails as $detail){
                $subscriptionPlan = SubscriptionPlan::where('stripe_price_id', $detail->subscription_plan_price_id)->first();

                if($detail->plan_interval == 'month'){
                    SubscriptionHelper::capture_monthly_pending_fees($detail->stripe_customer_id, $detail->user_id, $detail->user->name, $subscriptionPlan, $stripe);
                    SubscriptionHelper::renew_monthly_subscription($detail, $detail->user_id, $subscriptionPlan, $stripe);
                }
                else if($detail->plan_interval == 'year'){
                    SubscriptionHelper::capture_yearly_pending_fees($detail->stripe_customer_id, $detail->user_id, $detail->user->name, $subscriptionPlan, $stripe);
                    SubscriptionHelper::renew_yearly_subscription($detail, $detail->user_id, $subscriptionPlan, $stripe);
                }
                else if($detail->plan_interval == 'lifetime'){
                    SubscriptionHelper::renew_lifetime_subscription($detail, $detail->user_id, $detail->user->name, $subscriptionPlan, $stripe);
                }
            }
        }

    }
}
