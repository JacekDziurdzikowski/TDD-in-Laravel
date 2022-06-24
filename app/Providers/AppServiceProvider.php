<?php

namespace App\Providers;

use App\Billing\PaymentGateway;
use App\Billing\StripePaymentGateway;
use Illuminate\Support\ServiceProvider;
use Stripe\StripeClient;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        $this->app->bind(StripePaymentGateway::class, function() {
            return new StripePaymentGateway(new StripeClient(env('STRIPE_SECRET')));
        });

        $this->app->bind(PaymentGateway::class, StripePaymentGateway::class);
    }

    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        //
    }
}
