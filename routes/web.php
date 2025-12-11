<?php

// routes/web.php

use App\Http\Controllers\RegistrationController;
use App\Http\Controllers\PaymentController; // <-- ADD THIS LINE
use Illuminate\Support\Facades\Route;
use Illuminate\Http\Request;
use Inertia\Inertia;

// --- Application Pages ---

Route::get('/', function () {
    return Inertia::render('Index');
});

Route::get('/join', function () {
    return Inertia::render('join/Index');
});

// GET route to serve the registration form page
Route::get('/register', function () {
    return Inertia::render('auth/registration/Index', [
        'flash' => session()->only(['status', 'error']),
    ]);
});

Route::get('/sample', function () {
    return Inertia::render('auth/registration/sample');
});

// --- API Endpoint for Registration ---
Route::post('/api/register', [RegistrationController::class, 'register']);


// --- PayMongo Redirect Handlers ---

// 1. PayMongo Success URL (Verification Handler)
Route::get('/payment/verify', [RegistrationController::class, 'handlePaymentSuccess']);

// 2. Final Success Page (Inertia Renderer)
Route::get('/payment/success', [PaymentController::class, 'success']); // <-- UPDATED ROUTE

// 3. Failure/Cancel URL: PayMongo redirects the user here.
Route::get('/payment/failure', function (Request $request) {
    return Inertia::render('payment/Failure', [
        'teamId' => $request->query('team_id'),
    ]);
});

Route::get('/privacy-policy', function () {
    return Inertia::render('privacy-policy/Index');
});
