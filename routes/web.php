<?php

use Illuminate\Support\Facades\Route;

Route::get('/', fn () => auth()->check()
    ? redirect()->route('dashboard')
    : redirect()->route('login'))->name('home');

Route::inertia('/login', 'Login')->name('login');

Route::middleware('auth')->group(function (): void {
    Route::inertia('/dashboard', 'Dashboard')->name('dashboard');
    Route::inertia('/buildings', 'Buildings')->name('buildings');
    Route::inertia('/room-types', 'RoomTypes')->name('room-types');
    Route::inertia('/rooms', 'Rooms')->name('rooms');
    Route::inertia('/tenants', 'Tenants')->name('tenants');
    Route::inertia('/contracts', 'Contracts')->name('contracts');
    Route::inertia('/invoices', 'Invoices')->name('invoices');
    Route::inertia('/payments', 'Payments')->name('payments');
    Route::inertia('/utility-meters', 'UtilityMeters')->name('utility-meters');
    Route::inertia('/meter-readings', 'MeterReadings')->name('meter-readings');
    Route::inertia('/expenses', 'Expenses')->name('expenses');
    Route::inertia('/settings', 'Settings')->name('settings');
});
