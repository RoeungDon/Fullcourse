<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\ProductController;
use App\Http\Controllers\API\AuthController;

Route::apiResource('products', ProductController::class);

Route::post('/signup', [AuthController::class, 'signup']);
Route::post('/signin', [AuthController::class, 'signin']);

// Resend verification email (public)
Route::post('/email/verification-notification', [AuthController::class, 'sendVerificationEmail']);

Route::post('/send/reset-password-email', [AuthController::class, 'sendResetPasswordEmail']);
Route::post('/set/new-password', [AuthController::class, 'setNewPassword'])->name('set.new-password');

// Temporary signed email verification link (named verify.email for URL::temporarySignedRoute)
Route::get('/email/verify/{id}/{hash}', [AuthController::class, 'verifyEmail'])
    ->middleware('signed')
    ->name('verify.email');

Route::middleware('auth:sanctum')->group(function () {
    Route::post('/signout', [AuthController::class, 'signout']);
    Route::get('/verify', [AuthController::class, 'verify']);
});
