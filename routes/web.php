<?php

use App\Http\Controllers\AdminController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\OperatorController;
use App\Http\Controllers\PassengerController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| SURSEA - Lantsa Hub routes
|--------------------------------------------------------------------------
| 1. Public pages (anyone can open)
| 2. Login / Register / Logout
| 3. Passenger pages (must be logged in as passenger)
| 4. Operator portal (must be logged in as operator)
| 5. Admin portal (must be logged in as admin)
*/

// 1. PUBLIC PAGES
Route::get('/', [PassengerController::class, 'home'])->name('home');
Route::get('/schedules', [PassengerController::class, 'schedules'])->name('schedules');
Route::get('/routes', [PassengerController::class, 'routeList'])->name('routes');

// 2. LOGIN / REGISTER / LOGOUT
Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
Route::post('/login', [AuthController::class, 'login'])->name('login.submit');
Route::get('/register', [AuthController::class, 'showRegister'])->name('register');
Route::post('/register', [AuthController::class, 'register'])->name('register.submit');
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

// 3. PASSENGER PAGES
Route::middleware('role:passenger')->group(function () {
    Route::get('/waiting-list', [PassengerController::class, 'waitingList'])->name('waiting-list');

    Route::get('/reserve/{trip}', [PassengerController::class, 'seatForm'])->name('seat.form');
    Route::post('/reserve/{trip}', [PassengerController::class, 'seatStore'])->name('seat.store');

    Route::get('/waitlist/{trip}', [PassengerController::class, 'waitlistForm'])->name('waitlist.form');
    Route::post('/waitlist/{trip}', [PassengerController::class, 'waitlistStore'])->name('waitlist.store');

    Route::get('/cargo/{trip}', [PassengerController::class, 'cargoForm'])->name('cargo.form');
    Route::post('/cargo/{trip}', [PassengerController::class, 'cargoStore'])->name('cargo.store');
});

// 4. OPERATOR PORTAL
Route::middleware('role:operator')->prefix('operator')->name('operator.')->group(function () {
    Route::get('/', fn () => redirect()->route('operator.dashboard'));
    Route::get('/dashboard', [OperatorController::class, 'dashboard'])->name('dashboard');

    Route::post('/trip-schedule', [OperatorController::class, 'tripStore'])->name('trips.store');
    Route::post('/trip-schedule/{id}/status', [OperatorController::class, 'tripStatus'])->name('trips.status');

    Route::post('/waiting-list/{ref}/promote', [OperatorController::class, 'waitlistPromote'])->name('waitlist.promote');
    Route::post('/check-in/verify', [OperatorController::class, 'checkinVerify'])->name('checkin.verify');
    Route::get('/manifest/export', [OperatorController::class, 'manifestExport'])->name('manifest.export');

    Route::post('/cargo', [OperatorController::class, 'cargoStore'])->name('cargo.store');
    Route::post('/cargo/{ref}/status', [OperatorController::class, 'cargoStatus'])->name('cargo.status');

    Route::get('/reports/export', [OperatorController::class, 'reportsExport'])->name('reports.export');
    Route::post('/profile', [OperatorController::class, 'profileSave'])->name('profile.save');

    Route::get('/{module}', [OperatorController::class, 'module'])
        ->name('module')
        ->whereIn('module', [
            'vessel-routes', 'trip-schedule', 'waiting-list', 'check-in',
            'manifest', 'cargo', 'reports', 'notifications', 'profile',
        ]);
});

// 5. ADMIN PORTAL
Route::middleware('role:admin')->prefix('admin')->name('admin.')->group(function () {
    Route::get('/', fn () => redirect()->route('admin.dashboard'));
    Route::get('/dashboard', [AdminController::class, 'dashboard'])->name('dashboard');

    Route::get('/approvals', [AdminController::class, 'approvals'])->name('approvals');
    Route::get('/approvals/{id}', [AdminController::class, 'approvalShow'])->name('approvals.show');
    Route::post('/approvals/{id}', [AdminController::class, 'approvalDecide'])->name('approvals.decide');

    Route::get('/operators', [AdminController::class, 'operators'])->name('operators');
    Route::get('/operators/export', [AdminController::class, 'operatorsExport'])->name('operators.export');

    Route::get('/reports', [AdminController::class, 'reports'])->name('reports');
    Route::get('/reports/export', [AdminController::class, 'reportsExport'])->name('reports.export');

    Route::get('/settings', [AdminController::class, 'settings'])->name('settings');
    Route::post('/settings', [AdminController::class, 'settingsSave'])->name('settings.save');

    // Lantsa & Routes actions
    Route::post('/lantsa-routes', [AdminController::class, 'lantsaStore'])->name('lantsa.store');
    Route::post('/lantsa-routes/{id}/status', [AdminController::class, 'lantsaStatus'])->name('lantsa.status');

    // Trip Schedule actions
    Route::get('/trip-schedule/export', [AdminController::class, 'tripsExport'])->name('trips.export');
    Route::post('/trip-schedule', [AdminController::class, 'tripStore'])->name('trips.store');
    Route::post('/trip-schedule/{id}/status', [AdminController::class, 'tripStatus'])->name('trips.status');

    // Passenger Manifest actions
    Route::get('/passenger-manifest/export', [AdminController::class, 'manifestExport'])->name('manifest.export');
    Route::post('/passenger-manifest', [AdminController::class, 'manifestStore'])->name('manifest.store');
    Route::post('/passenger-manifest/verify', [AdminController::class, 'manifestVerify'])->name('manifest.verify');
    Route::post('/passenger-manifest/{ref}/status', [AdminController::class, 'manifestStatus'])->name('manifest.status');

    // Cargo actions
    Route::get('/cargo/export', [AdminController::class, 'cargoExport'])->name('cargo.export');
    Route::post('/cargo', [AdminController::class, 'cargoStore'])->name('cargo.store');
    Route::post('/cargo/{ref}/status', [AdminController::class, 'cargoStatus'])->name('cargo.status');

    Route::get('/{module}', [AdminController::class, 'module'])
        ->name('module')
        ->whereIn('module', [
            'lantsa-routes', 'trip-schedule', 'passenger-manifest', 'cargo', 'notifications',
        ]);
});
