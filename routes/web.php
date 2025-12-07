<?php

// routes/web.php

use App\Http\Controllers\RegistrationController;
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
    // Passes flash messages to Inertia
    return Inertia::render('auth/registration/Index', [
        'flash' => session()->only(['status', 'error']),
    ]);
});

Route::get('/sample', function () {
    return Inertia::render('auth/registration/sample');
});

// --- API Endpoint for Registration ---

// POST route that handles the team registration and initiates PayMongo session
Route::post('/api/register', [RegistrationController::class, 'register']);


// --- PayMongo Redirect Handlers ---

// 1. Success URL: PayMongo redirects the user here. The controller verifies and redirects to /register.
Route::get('/payment/success', [RegistrationController::class, 'handlePaymentSuccess']);

// 2. Failure/Cancel URL: PayMongo redirects the user here.
Route::get('/payment/failure', function (Request $request) {
    return Inertia::render('payment/Failure', [
        'teamId' => $request->query('team_id'),
    ]);
});
