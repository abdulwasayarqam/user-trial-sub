<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\SubscriptionPlan;
use App\Models\SubscriptionDetail;
use App\Models\CardDetail;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Stripe\Stripe;
use Stripe\Customer;
use Stripe\StripeClient;
use Barryvdh\DomPDF\Facade\PDF;
use App\Helpers\SubscriptionHelper;
use App\Mail\SubscriptionConfirmation;

class SubscriptionController extends Controller
{
    public function loadSubscription()
    {
        $plans = SubscriptionPlan::where('enabled', 1)->get();
        return view('subscription', compact('plans'));
    }

    public function getPlanDetails(Request $request)
    {
        try {
            $planData = SubscriptionPlan::where('id', $request->id)->first();
            $haveAnyActivePlan = SubscriptionDetail::where(['user_id' => auth()->user()->id, 'status' => 'active'])->count();
            $msg = '';

            if ($haveAnyActivePlan == 0 && ($planData->trial_days != null && $planData->trial_days != '')) {
                $msg = "You will get " . $planData->trial_days . " days trial, and after we will charge $" . $planData->amount . " for " . $planData->name . " Subscription Plan.";
            } else {
                $msg = "We will charge $" . $planData->amount . " for " . $planData->name . " Subscription Plan.";
            }

            return response()->json(['success' => true, 'msg' => $msg, 'data' => $planData]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'msg' => $e->getMessage()]);
        }
    }

    public function createSubscription(Request $request)
    {
        try {
            $user = auth()->user();
            $user_id = $user->id;
            $secretKey = env('STRIPE_SECRET_KEY');
            Stripe::setApiKey($secretKey);
            $stripeData = $request->data;

            $subscriptionData = null;
            $stripe = new StripeClient($secretKey);

            $subscriptionDetail = SubscriptionDetail::where('user_id', $user_id)->first();

            if (!$subscriptionDetail || !$subscriptionDetail->stripe_customer_id) {
                $customer = $this->getOrCreateCustomer($stripeData['id'], $user_id);

                if ($subscriptionDetail) {
                    $subscriptionDetail->stripe_customer_id = $customer->id;
                    $subscriptionDetail->save();
                } else {
                    SubscriptionDetail::create([
                        'user_id' => $user_id,
                        'stripe_customer_id' => $customer->id,
                        'status' => 'active',
                        'cancel' => 0,
                        'subscription_plan_price_id' => $request->plan_id,
                        'plan_amount' => $request->plan_amount,
                        'plan_amount_currency' => 'usd',
                        'plan_interval' => 'monthly',
                        'plan_interval_count' => 1,
                        'created_at' => now(),
                        'updated_at' => now()
                    ]);
                }
            } else {
                $customer = $stripe->customers->retrieve($subscriptionDetail->stripe_customer_id);
            }

            $customer_id = $customer->id;
            $subscriptionPlan = SubscriptionPlan::where('id', $request->plan_id)->first();

            $subscriptionDetail = SubscriptionDetail::where([
                'user_id' => $user_id,
                'status' => 'active',
                'cancel' => 0
            ])->orderBy('id', 'desc')->first();

            $subscriptionDetailsCount = SubscriptionDetail::where(['user_id' => $user_id])->orderBy('id', 'desc')->count();

            if ($subscriptionDetail && $subscriptionDetail->plan_interval == 'month' && $subscriptionPlan->type == 1) {
                SubscriptionHelper::cancel_current_subscription($user_id, $subscriptionDetail);
                $subscriptionData = SubscriptionHelper::start_yearly_subscription($customer_id, $user_id, $subscriptionPlan, $stripe);
            } else if ($subscriptionDetail && $subscriptionDetail->plan_interval == 'month' && $subscriptionPlan->type == 2) {
                SubscriptionHelper::cancel_current_subscription($user_id, $subscriptionDetail);
                $subscriptionData = SubscriptionHelper::start_lifetime_subscription($customer_id, $user_id, $user->name, $subscriptionPlan, $stripe);
            } else if ($subscriptionDetail && $subscriptionDetail->plan_interval == 'year' && $subscriptionPlan->type == 0) {
                SubscriptionHelper::cancel_current_subscription($user_id, $subscriptionDetail);
                $subscriptionData = SubscriptionHelper::start_monthly_subscription($customer_id, $user_id, $subscriptionPlan, $stripe);
            } else if ($subscriptionDetail && $subscriptionDetail->plan_interval == 'year' && $subscriptionPlan->type == 2) {
                SubscriptionHelper::cancel_current_subscription($user_id, $subscriptionDetail);
                $subscriptionData = SubscriptionHelper::start_lifetime_subscription($customer_id, $user_id, $user->name, $subscriptionPlan, $stripe);
            } else {
                if ($subscriptionDetailsCount == 0) {
                    if ($subscriptionPlan->type == 0) {
                        $subscriptionData = SubscriptionHelper::start_monthly_trial_subscription($customer_id, $user_id, $subscriptionPlan);
                    } else if ($subscriptionPlan->type == 1) {
                        $subscriptionData = SubscriptionHelper::start_yearly_trial_subscription($customer_id, $user_id, $subscriptionPlan);
                    } else if ($subscriptionPlan->type == 2) {
                        $subscriptionData = SubscriptionHelper::start_lifetime_trial_subscription($customer_id, $user_id, $subscriptionPlan);
                    }
                } else {
                    if ($subscriptionPlan->type == 0) {
                        SubscriptionHelper::capture_monthly_pending_fees($customer_id, $user_id, $user->name, $subscriptionPlan, $stripe);
                        $subscriptionData = SubscriptionHelper::start_monthly_subscription($customer_id, $user_id, $subscriptionPlan, $stripe);
                    } else if ($subscriptionPlan->type == 1) {
                        SubscriptionHelper::capture_yearly_pending_fees($customer_id, $user_id, $user->name, $subscriptionPlan, $stripe);
                        $subscriptionData = SubscriptionHelper::start_yearly_subscription($customer_id, $user_id, $subscriptionPlan, $stripe);
                    } else if ($subscriptionPlan->type == 2) {
                        $subscriptionData = SubscriptionHelper::start_lifetime_subscription($customer_id, $user_id, $user->name, $subscriptionPlan, $stripe);
                    }
                }
            }

            $this->saveCardDetails($stripeData, $user_id, $customer_id);

            if ($subscriptionData) {
                $pdf = $this->generateSubscriptionPDF($user, $subscriptionPlan);
                Mail::to($user->email)->send(new SubscriptionConfirmation($user, $subscriptionPlan->name, $pdf));
                return response()->json(['success' => true, 'msg' => 'Subscription Purchased!']);
            }  else {
                return response()->json(['success' => false, 'msg' => 'Subscription Purchase Failed!']);
            }

        } catch (\Exception $e) {
            return response()->json(['success' => false, 'msg' => $e->getMessage()]);
        }
    }

    public function getOrCreateCustomer($token_id, $user_id)
    {
        $stripe = new StripeClient(env('STRIPE_SECRET_KEY'));

        $subscriptionDetail = SubscriptionDetail::where('user_id', $user_id)->first();

        if ($subscriptionDetail && $subscriptionDetail->stripe_customer_id) {
            $customer = $stripe->customers->retrieve($subscriptionDetail->stripe_customer_id);
        } else {
            $customer = $stripe->customers->create([
                'name' => auth()->user()->name,
                'email' => auth()->user()->email,
                'source' => $token_id,
            ]);
        }

        return $customer;
    }

    public function saveCardDetails($cardData, $user_id, $customer_id)
    {
        CardDetail::updateOrCreate(
            [
                'user_id' => $user_id,
                'card_no' => $cardData['card']['last4'],
            ],
            [
                'user_id' => $user_id,
                'customer_id' => $customer_id,
                'card_no' => $cardData['card']['last4'],
                'card_brand' => $cardData['card']['brand'],
                'month' => $cardData['card']['exp_month'],
                'year' => $cardData['card']['exp_year'],
                'created_at' => now(),
                'updated_at' => now()
            ]
        );
    }

    public function cancelSubscription(Request $request)
{
    // Validate the request input
    $request->validate([
        'stripe_subscription_id' => 'required|string',
    ]);

    $stripeSubscriptionId = $request->input('stripe_subscription_id');

    // Cancel the subscription on Stripe
    $stripe = new StripeClient(env('STRIPE_SECRET_KEY'));
    try {
        $stripe->subscriptions->cancel($stripeSubscriptionId);
    } catch (\Exception $e) {
        return response()->json(['success' => false, 'msg' => 'Stripe subscription cancellation failed: ' . $e->getMessage()], 500);
    }

    // Update the subscription status in the local database
    $affected = DB::table('subscription_details')
        ->where('stripe_subscription_id', $stripeSubscriptionId)
        ->update([
            'status' => 'cancelled',
            'cancel' => 1,
            'canceled_at' => now(),
            'updated_at' => now(),
        ]);

    if ($affected) {
        return response()->json(['success' => true, 'msg' => 'Subscription canceled successfully.'], 200);
    } else {
        return response()->json(['success' => false, 'msg' => 'Subscription not found or already canceled.'], 404);
    }
}



    public function webhookSubscription(Request $request)
    {
        $endpoint_secret = env('STRIPE_WEBHOOK_SECRET');
        $payload = @file_get_contents('php://input');
        $sig_header = $_SERVER['HTTP_STRIPE_SIGNATURE'];
        $event = null;

        try {
            $event = \Stripe\Webhook::constructEvent(
                $payload, $sig_header, $endpoint_secret
            );
        } catch (\UnexpectedValueException $e) {
            Log::error('Invalid payload: ' . $e->getMessage());
            return response()->json(['error' => 'Invalid payload'], 400);
        } catch (\Stripe\Exception\SignatureVerificationException $e) {
            Log::error('Invalid signature: ' . $e->getMessage());
            return response()->json(['error' => 'Invalid signature'], 400);
        } catch (\Exception $e) {
            Log::error('General error: ' . $e->getMessage());
            return response()->json(['error' => 'An error occurred'], 500);
        }

        Log::info('Stripe event received', ['event' => $event]);

        switch ($event->type) {
            case 'customer.subscription.deleted':
                $subscription = $event->data->object;
                SubscriptionDetail::where('stripe_subscription_id', $subscription->id)->update([
                    'status' => 'cancelled', // Change to 'cancelled'
                    'cancel' => 1,
                    'canceled_at' => now(),
                    'updated_at' => now()
                ]);
                break;

            case 'customer.subscription.updated':
                $subscription = $event->data->object;
                $customer_id = $subscription->customer;
                SubscriptionDetail::where('stripe_customer_id', $customer_id)->update([
                    'cancel' => 1,
                    'canceled_at' => Carbon::now()
                ]);
                Log::info('Subscription updated event', ['subscription' => $subscription]); // Log the event object

                $status = $subscription->status;
                if ($status === 'canceled' || $status === 'incomplete_expired') {
                    $subscriptionDetail = SubscriptionDetail::where('stripe_subscription_id', $subscription->id)->first();
                    if ($subscriptionDetail) {
                        $subscriptionDetail->update([
                            'status' => 'cancelled', // Change to 'cancelled'
                            'cancel' => 1,
                            'updated_at' => now()
                        ]);
                    }
                }
                break;

            default:
                Log::warning('Unhandled event type: ' . $event->type);
                return response()->json(['error' => 'Unhandled event type'], 400);
        }

        return response()->json(['success' => true], 200);
    }
    private function generateSubscriptionPDF($user, $subscriptionPlan)
{
    $data = [
        'user' => $user,
        'plan' => $subscriptionPlan,
        'date' => Carbon::now()->toFormattedDateString()
    ];

    $pdf = PDF::loadView('emails.subscription_pdf', $data);
    return $pdf->output();
}
}
