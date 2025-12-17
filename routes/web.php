<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\MapController;
use App\Http\Controllers\DataSetController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\DashboardController;

Route::get('/', function () {
    return view('pages.dashboard.index');
});

Route::group(['prefix' => '/', 'middleware' => ['auth']], function () {
    Route::get('/', [DashboardController::class, 'index',['title', 'Dashboard']])->name('dashboard');

    Route::resource('datasets', DataSetController::class);
    Route::get('datasets/template/download', [DataSetController::class, 'downloadTemplate'])->name('datasets.template');

    Route::get('maps', [MapController::class, 'index'])->name('maps.index');
    Route::get('maps/api/points', [MapController::class, 'getTransactionPoints'])->name('maps.api.points');
    Route::get('maps/api/boundaries', [MapController::class, 'getBoundaries'])->name('maps.api.boundaries');
    Route::get('maps/api/statistics', [MapController::class, 'getStatistics'])->name('maps.api.statistics');

    Route::group(['prefix' => 'settings'], function () {
        Route::resource('users', App\Http\Controllers\UserController::class);
        Route::resource('roles', App\Http\Controllers\RoleController::class);
        Route::resource('permissions', App\Http\Controllers\PermissionController::class);

        Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
        Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
        Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
    });
});



// Route::middleware('auth')->group(function () {
//     Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
//     Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
//     Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
// });

require __DIR__.'/auth.php';


// Ref


// calender pages
Route::get('/calendar', function () {
    return view('pages.calender', ['title' => 'Calendar']);
})->name('calendar');

// profile pages
Route::get('/profile', function () {
    return view('pages.profile', ['title' => 'Profile']);
})->name('profile');

// form pages
Route::get('/form-elements', function () {
    return view('pages.form.form-elements', ['title' => 'Form Elements']);
})->name('form-elements');

// tables pages
Route::get('/basic-tables', function () {
    return view('pages.tables.basic-tables', ['title' => 'Basic Tables']);
})->name('basic-tables');

// pages

Route::get('/blank', function () {
    return view('pages.blank', ['title' => 'Blank']);
})->name('blank');

// error pages
Route::get('/error-404', function () {
    return view('pages.errors.error-404', ['title' => 'Error 404']);
})->name('error-404');

// chart pages
Route::get('/line-chart', function () {
    return view('pages.chart.line-chart', ['title' => 'Line Chart']);
})->name('line-chart');

Route::get('/bar-chart', function () {
    return view('pages.chart.bar-chart', ['title' => 'Bar Chart']);
})->name('bar-chart');


// authentication pages
Route::get('/signin', function () {
    return view('pages.auth.signin', ['title' => 'Sign In']);
})->name('signin');

Route::get('/signup', function () {
    return view('pages.auth.signup', ['title' => 'Sign Up']);
})->name('signup');

// ui elements pages
Route::get('/alerts', function () {
    return view('pages.ui-elements.alerts', ['title' => 'Alerts']);
})->name('alerts');

Route::get('/avatars', function () {
    return view('pages.ui-elements.avatars', ['title' => 'Avatars']);
})->name('avatars');

Route::get('/badge', function () {
    return view('pages.ui-elements.badges', ['title' => 'Badges']);
})->name('badges');

Route::get('/buttons', function () {
    return view('pages.ui-elements.buttons', ['title' => 'Buttons']);
})->name('buttons');

Route::get('/image', function () {
    return view('pages.ui-elements.images', ['title' => 'Images']);
})->name('images');

Route::get('/videos', function () {
    return view('pages.ui-elements.videos', ['title' => 'Videos']);
})->name('videos');










