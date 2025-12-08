<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Stripe\StripeClient;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {

        /*
        nakonec nepoužito, ale ponechano pro inspiraci
        $this->app->bind(ResultService::class, function ($app) {

            if ($app->request->eventId == null) {
                throw new \Exception('Není uvedeno Id závodu.');
            }

            if (!is_numeric($app->request->eventId)) {
                throw new \InvalidArgumentException('Id závodu musí být číslo.');
            }

            return new ResultService($app->request->eventId);
        });*/

    $this->app->singleton(\Stripe\StripeClient::class, function () {
        return new \Stripe\StripeClient(config('services.stripe.secret'));
    });


    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        //
    }
}
