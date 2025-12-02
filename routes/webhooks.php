<?php

use App\Http\Controllers\RegistrationController;
use App\Http\Controllers\StravaController;
use Illuminate\Support\Facades\Route;

// Stripe webhook - BEZ middleware (bez CSRF, sessions, cookies)
Route::post('/webhook/stripe', [RegistrationController::class, 'handleWebhook'])
    ->name('stripe.webhook');

// Strava webhooks - BEZ middleware (bez CSRF, sessions, cookies)
Route::get('/webhook', [StravaController::class, 'getStrava'])
    ->name('get_strava');
Route::post('/webhook', [StravaController::class, 'webhookPostStrava'])
    ->name('post_strava');
