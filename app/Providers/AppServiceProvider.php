<?php

namespace App\Providers;
use Stripe\Stripe;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
        $this->app->singleton('subscription_helper', function(){
            return new \App\Helpers\SubscriptionHelper();
        });
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        //
        Stripe::setApiKey(config('services.stripe.secret'));
    }
}
