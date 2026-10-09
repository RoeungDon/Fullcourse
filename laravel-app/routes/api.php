<?php

use App\Http\Controllers\API\AuthController;
use App\Http\Controllers\API\GoogleOAuthController;
use App\Http\Controllers\API\ProductController;
use App\Http\Controllers\API\UserController;
use Illuminate\Support\Facades\Route;

Route::controller(AuthController::class)->group(function () {
    Route::post('/signup', 'signup');
    Route::post('/signin', 'signin');
    Route::post('/send/verification-email', 'sendVerificationEmail');
    Route::post('/verify/email', 'verifyEmail');
    Route::post('/send/reset-password-email', 'sendResetPasswordEmail');
    Route::post('/reset/password', 'resetPassword');
});

Route::controller(GoogleOAuthController::class)->group(function () {
    Route::get('/google/oauth/redirect', 'googleOAuthRedirect');
    Route::get('/google/oauth/callback', 'googleOAuthCallback');
    Route::post('/google/oauth/exchange/token', 'googleOAuthExchangeToken')->middleware('auth:sanctum');
});

Route::middleware('auth:sanctum')->group(function () {
    Route::post('/signout', [AuthController::class, 'signout']);
    Route::get('/verify', [AuthController::class, 'verify']);
    Route::put('/create/password', [AuthController::class, 'createPassword']);
    Route::put('/change/password', [AuthController::class, 'changePassword']);
    Route::post('/update/profile-image', [AuthController::class, 'updateProfileImage']);
    Route::delete('/delete/profile-image', [AuthController::class, 'deleteProfileImage']);
    Route::apiResource('products', ProductController::class);
});

Route::middleware(['auth:sanctum', 'admin'])->group(function () {
    Route::get('/users', [UserController::class, 'getUsers']);
    Route::get('/users/{id}', [UserController::class, 'readUser']);
    Route::post('/users', [UserController::class, 'createUser']);
    Route::put('/users/{id}', [UserController::class, 'updateUser']);
    Route::delete('/users/{id}', [UserController::class, 'deleteUser']);
});
