<?php

use App\Http\Controllers\BookingController;
use App\Http\Controllers\CustomerController;
use App\Http\Controllers\OnboardingController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\ScheduleController;
use App\Http\Controllers\StaffController;
use App\Http\Controllers\TreatmentCategoryController;
use App\Http\Controllers\TreatmentController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/dashboard', function () {
    return view('dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::post('/onboarding/check-subdomain', [OnboardingController::class, 'checkSubdomain'])->name('onboarding.check');
    Route::post('/onboarding/submit', [OnboardingController::class, 'submit'])->name('onboarding.submit');

    // Treatment Category Routes
    Route::prefix('treatment/categories')->name('treatment.categories.')->group(function () {
        Route::get('/', [TreatmentCategoryController::class, 'index'])->name('index');
        Route::post('/load-data', [TreatmentCategoryController::class, 'load_data'])->name('load_data');
        Route::post('/store', [TreatmentCategoryController::class, 'store'])->name('store');
        Route::get('/{id}/edit', [TreatmentCategoryController::class, 'edit'])->name('edit');
        Route::put('/{id}', [TreatmentCategoryController::class, 'update'])->name('update');
        Route::delete('/{id}', [TreatmentCategoryController::class, 'destroy'])->name('destroy');
        Route::patch('/{id}/toggle-status', [TreatmentCategoryController::class, 'toggle_status'])->name('toggle_status');
    });

    // Treatment Routes
    Route::prefix('treatment/list')->name('treatment.list.')->group(function () {
        Route::get('/', [TreatmentController::class, 'index'])->name('index');
        Route::post('/load-data', [TreatmentController::class, 'load_data'])->name('load_data');
        Route::post('/store', [TreatmentController::class, 'store'])->name('store');
        Route::get('/{id}/edit', [TreatmentController::class, 'edit'])->name('edit');
        Route::put('/{id}', [TreatmentController::class, 'update'])->name('update');
        Route::delete('/{id}', [TreatmentController::class, 'destroy'])->name('destroy');
        Route::delete('/sub-image/{id}', [TreatmentController::class, 'delete_sub_image'])->name('delete_sub_image');
        Route::delete('/{id}/thumbnail', [TreatmentController::class, 'delete_thumbnail'])->name('delete_thumbnail');
        Route::patch('/{id}/toggle-status', [TreatmentController::class, 'toggle_status'])->name('toggle_status');
    });

    // Staff Routes
    Route::prefix('staff/list')->name('staff.list.')->group(function () {
        Route::get('/', [StaffController::class, 'index'])->name('index');
        Route::post('/load-data', [StaffController::class, 'load_data'])->name('load_data');
        Route::post('/store', [StaffController::class, 'store'])->name('store');
        Route::get('/{id}/edit', [StaffController::class, 'edit'])->name('edit');
        Route::put('/{id}', [StaffController::class, 'update'])->name('update');
        Route::delete('/{id}', [StaffController::class, 'destroy'])->name('destroy');
        Route::patch('/{id}/toggle-status', [StaffController::class, 'toggle_status'])->name('toggle_status');
        Route::delete('/{id}/photo', [StaffController::class, 'delete_photo'])->name('delete_photo');
    });

    Route::prefix('staff/schedule')->name('staff.schedule.')->group(function () {
        Route::get('/', [ScheduleController::class, 'index'])->name('index');
        Route::post('/operating-hours', [ScheduleController::class, 'saveOperatingHours'])->name('operating_hours');

        Route::get('/staff-schedule/{staff_id}', [ScheduleController::class, 'getStaffSchedule'])->name('get_staff_schedule');
        Route::post('/staff-schedule/{staff_id}', [ScheduleController::class, 'saveStaffSchedule'])->name('save_staff_schedule');

        Route::post('/time-off', [ScheduleController::class, 'saveTimeOff'])->name('save_time_off');
        Route::post('/time-off/data', [ScheduleController::class, 'timeOffData'])->name('time_off_data');
        Route::delete('/time-off/{id}', [ScheduleController::class, 'deleteTimeOff'])->name('delete_time_off');
    });

    // Customer Routes
    Route::prefix('customer')->name('customer.')->group(function () {
        Route::get('/', [CustomerController::class, 'index'])->name('index');
        Route::post('/load-data', [CustomerController::class, 'load_data'])->name('load_data');
        Route::post('/store', [CustomerController::class, 'store'])->name('store');
        Route::get('/{id}/edit', [CustomerController::class, 'edit'])->name('edit');
        Route::put('/{id}', [CustomerController::class, 'update'])->name('update');
        Route::delete('/{id}', [CustomerController::class, 'destroy'])->name('destroy');
        Route::get('/{id}/profile', [CustomerController::class, 'profile'])->name('profile');
    });

    // Booking Routes
    Route::prefix('booking')->name('booking.')->group(function () {
        Route::get('/', [BookingController::class, 'index'])->name('index');
        Route::post('/load-data', [BookingController::class, 'load_data'])->name('load_data');
        Route::post('/store', [BookingController::class, 'store'])->name('store');
        Route::get('/{id}/edit', [BookingController::class, 'edit'])->name('edit');
        Route::put('/{id}', [BookingController::class, 'update'])->name('update');
        Route::patch('/{id}/status', [BookingController::class, 'updateStatus'])->name('update_status');
        Route::get('/get-available-times', [BookingController::class, 'getAvailableTimes'])->name('get_available_times');
    });

    // Calendar Routes
    Route::prefix('calendar')->name('calendar.')->group(function () {
        Route::get('/', [\App\Http\Controllers\CalendarController::class, 'index'])->name('index');
        Route::get('/events', [\App\Http\Controllers\CalendarController::class, 'events'])->name('events');
    });

    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__.'/auth.php';
