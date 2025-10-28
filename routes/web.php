<?php

use Illuminate\Support\Facades\Route;
use Inertia\Inertia;
use Laravel\Fortify\Features;

Route::get('/', function () {
    return Inertia::render('Welcome', [
        'canRegister' => Features::enabled(Features::registration()),
    ]);
})->name('home');

Route::get('dashboard', function () {
    return Inertia::render('Dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

Route::get('schedule', function () {
    return Inertia::render('Schedule');
})->middleware(['auth', 'verified'])->name('schedule');
    
Route::get('history', function () {
    return Inertia::render('History');
})->middleware(['auth', 'verified'])->name('history');

Route::get('clinics', function () {
    return Inertia::render('Clinics');
})->middleware(['auth', 'verified'])->name('clinics');

Route::get('clinic/{id}', function ($id) {
    return Inertia::render('clinics/clinicViewDetails', [
        'clinicId' => $id
    ]);
})->middleware(['auth', 'verified'])->name('clinicDetails');

Route::get('pet', function () {
    return Inertia::render('Pet');
})->middleware(['auth', 'verified'])->name('pet');

Route::get('pet/add', function () {
    return Inertia::render('pets/addPet');
})->middleware(['auth', 'verified'])->name('addPet');

Route::get('map', function () {
    return Inertia::render('maps/viewMap');
})->middleware(['auth', 'verified'])->name('viewMap');

Route::get('appointment/{id}', function ($id) {
    return Inertia::render('Scheduling/AppointmentDetails', [
        'appointmentId' => $id
    ]);
})->middleware(['auth', 'verified'])->name('appointmentDetails');

Route::get('appointment/{id}/reschedule', function ($id) {
    return Inertia::render('Scheduling/RescheduleAppointment', [
        'appointmentId' => $id
    ]);
})->middleware(['auth', 'verified'])->name('rescheduleAppointment');

Route::get('calendar', function () {
    return Inertia::render('Scheduling/AppointmentCalendar');
})->middleware(['auth', 'verified'])->name('appointmentCalendar');

Route::get('booking', function () {
    return Inertia::render('Scheduling/Booking', [
        'clinicId' => request('clinic_id'),
        'clinicName' => request('clinic_name'),
    ]);
})->middleware(['auth', 'verified'])->name('booking');

require __DIR__.'/settings.php';
