<?php

use App\Http\Controllers\api\AuthController;
use Illuminate\Support\Facades\Route;


use App\Models\Goal;
use App\Mail\AdminGoalNotification;
use App\Mail\UserThankYouMail;



Route::get('/test-user-email', function () {
    $goal = Goal::latest()->first();
    return new UserThankYouMail($goal);
});

Route::get('/test-admin-email', function () {
    $goal = Goal::latest()->first();
    return new AdminGoalNotification($goal);
});


// Route::get('/', function () {
//     return redirect()->away('https://prime.ajurnie.com/');
// });


// Route::any('{any}', function () {
//     return redirect()->away('https://prime.ajurnie.com/');
// })->where('any', '.*');


Route::get('/', [AuthController::class, 'index'])->name('auth.index');

