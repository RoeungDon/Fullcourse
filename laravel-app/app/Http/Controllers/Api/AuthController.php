<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Http\Requests\User\SendVerificationEmailRequest;
use App\Http\Requests\User\SigninRequest;
use App\Http\Requests\User\SignupRequest;
use App\Http\Resources\User\UserResource;
use App\Models\User;
use Illuminate\Auth\Events\Verified;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;
use App\Http\Requests\User\SendResetPasswordEmailRequest;
use App\Http\Requests\User\SetNewPasswordRequest;
use Illuminate\Support\Facades\Password;

class AuthController extends Controller
{
    function signup(SignupRequest $request)
    {
        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => $request->password,
        ]);

        // Send verification email with SPA callback URL in the button
        $user->sendEmailVerificationNotification($request->callback_url);

        return response([
            'message' => 'User signed up. Please check your email to verify your account.',
            'user' => new UserResource($user),
        ], 201);
    }

    function signin(SigninRequest $request)
    {
        $user = User::where('email', $request->email)->first();

        if (!Hash::check($request->password, $user->password)) {
            throw ValidationException::withMessages([
                'password' => 'Password does not match.',
            ]);
        }

        $token = $user->createToken('auth_token')->plainTextToken;

        return response([
            'message' => 'User signed in.',
            'user' => new UserResource($user),
            'token' => $token,
        ], 200);
    }

    function signout(Request $request)
    {
        $user = $request->user();

        // option 1
        $user->currentAccessToken()->delete();

        // option 2
        // $currentToken = $user->currentAccessToken();
        // $user->tokens()->where('id', $currentToken->id)->delete();

        return response([
            'message' => 'User signed out.',
        ], 200);
    }

    /**
     * Check Sanctum bearer token is still valid.
     */
    function verify(Request $request)
    {
        return response([
            'message' => 'Token is valid.',
            'user' => new UserResource($request->user()),
        ], 200);
    }

    /**
     * Resend verification email (public — body has email + callback_url).
     */
    function sendVerificationEmail(SendVerificationEmailRequest $request)
    {
        $user = User::where('email', $request->email)->first();

        if ($user->hasVerifiedEmail()) {
            return response([
                'message' => 'Email already verified.',
            ], 200);
        }

        $user->sendEmailVerificationNotification($request->callback_url);

        return response([
            'message' => 'Verification email sent.',
        ], 200);
    }

    /**
     * Mark email verified from temporary signed URL
     * (GET /api/email/verify/{id}/{hash}?expires=...&signature=...).
     */
    function verifyEmail(Request $request, $id, $hash)
    {
        $user = User::findOrFail($id);

        if (!hash_equals((string) $hash, sha1($user->getEmailForVerification()))) {
            return response([
                'message' => 'Invalid verification link.',
            ], 403);
        }

        if ($user->hasVerifiedEmail()) {
            return response([
                'message' => 'Email already verified.',
                'user' => new UserResource($user),
            ], 200);
        }

        if ($user->markEmailAsVerified()) {
            event(new Verified($user));
        }

        return response([
            'message' => 'Email verified successfully.',
            'user' => new UserResource($user),
        ], 200);
    }
    function sendResetPasswordEmail(SendResetPasswordEmailRequest $request)
{
    $status = Password::sendResetLink(
        ['email' => $request->email],
        function ($user, $token) use ($request) {
            $user->sendPasswordResetNotification($token, $request->callback_url);
        }
    );

    if ($status === Password::RESET_LINK_SENT) {
        return response([
            'message' => 'Password reset link sent to your email'
        ], 200);
    }

    return response([
        'message' => 'Password reset link sent to your email'
    ], 200);
    }
    function setNewPassword(SetNewPasswordRequest $request)
{
    $status = Password::reset(
        [
            'token' => $request->token,
            'email' => $request->email,
            'password' => $request->password,
            'password_confirmation' => $request->password_confirmation,
        ],
        function ($user, $password) {
            $user->password = $password;
            $user->save();
            $user->tokens()->delete();
        }
    );

    if ($status !== Password::PASSWORD_RESET) {
        throw ValidationException::withMessages([
            'password' => [__($status)],
        ]);
    }

    return response([
        'message' => 'Password has been reset successfully.'
    ], 200);
    }


}
