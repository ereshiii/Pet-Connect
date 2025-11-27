<?php

use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

Route::get('/debug/object-test', function () {
    return Inertia::render('Debug/ObjectTest');
})->name('debug.object-test');
