<?php

use Illuminate\Support\Facades\Route;
use Inertia\Inertia;


Route::get('/', function () {
    return Inertia::render('Index');
});

Route::get('/join', function () {
    return Inertia::render('join/Index');
});

Route::get('/register', function () {
    return Inertia::render('auth/registration/Index');
});
