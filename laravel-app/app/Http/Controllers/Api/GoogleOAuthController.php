<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Http\Resources\User\UserResource;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Laravel\Socialite\Facades\Socialite;

class GoogleOAuthController extends Controller
{
    function googleOAuthRedirect(Request $request)
    {
        $callback_url = $request->query('callback_url', '');

        if ($callback_url === '') {
            return response()->json([
                'message' => 'callback_url is required.',
            ], 422);
        }

        $redirectUrl = Socialite::driver('google')
            ->stateless()
            ->with(['state' => base64_encode($callback_url)])
            ->redirect()
            ->getTargetUrl();

        return response()->json(['redirect_url' => $redirectUrl], 200);
    }

    function googleOAuthCallback(Request $request)
    {
        $frontendCallback = base64_decode($request->query('state', ''), true) ?: '';

        // Fallback so we never redirect to a blank URL
        if ($frontendCallback === '' || !str_starts_with($frontendCallback, 'http')) {
            $frontendCallback = rtrim(env('FRONTEND_URL', 'http://localhost:5173'), '/')
                . '/google/oauth/callback';
        }

        try {
            $googleUser = Socialite::driver('google')->stateless()->user();
        } catch (\Throwable $e) {
            Log::error('Google OAuth Socialite failed', [
                'message' => $e->getMessage(),
                'exception' => get_class($e),
                'redirect_config' => config('services.google.redirect'),
                'client_id_set' => filled(config('services.google.client_id')),
                'query' => $request->query(),
            ]);

            return redirect($frontendCallback . '?error=google_oauth_failed&detail=' . urlencode(
                config('app.debug') ? $e->getMessage() : 'token_exchange_failed'
            ));
        }

        try {
            $email = $googleUser->getEmail();
            if (!$email) {
                throw new \RuntimeException('Google account did not return an email address.');
            }

            $user = User::firstOrCreate(
                ['email' => $email],
                [
                    'name' => $googleUser->getName() ?: $email,
                    'level' => 'USER',
                    // password stays null for Google-only accounts (migration made it nullable)
                ]
            );

            if (!$user->hasVerifiedEmail()) {
                $user->markEmailAsVerified();
            }

            $token = $user->createToken(
                'auth_token',
                ['exchange-new-token'],
                now()->addMinute()
            )->plainTextToken;

            return redirect($frontendCallback . '?token=' . urlencode($token));
        } catch (\Throwable $e) {
            Log::error('Google OAuth user provisioning failed', [
                'message' => $e->getMessage(),
                'exception' => get_class($e),
                'email' => $googleUser->getEmail(),
            ]);

            return redirect($frontendCallback . '?error=google_oauth_failed&detail=' . urlencode(
                config('app.debug') ? $e->getMessage() : 'user_provision_failed'
            ));
        }
    }

    function googleOAuthExchangeToken(Request $request)
    {
        $user = $request->user();

        if (!$user->currentAccessToken()->can('exchange-new-token')) {
            return response()->json([
                'message' => 'Invalid token ability.',
            ], 403);
        }

        // One-time exchange token → long-lived API token
        $user->currentAccessToken()->delete();

        $token = $user->createToken('auth_token')->plainTextToken;

        return response()->json([
            'user' => new UserResource($user),
            'token' => $token,
        ], 200);
    }
}
