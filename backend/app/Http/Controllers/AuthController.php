<?php

namespace App\Http\Controllers;

use App\Models\ProjectShare;
use App\Models\User;
use Illuminate\Http\Request;
use Laravel\Socialite\Facades\Socialite;

class AuthController extends Controller
{
    private function socialiteGoogle(): \Laravel\Socialite\Two\GoogleProvider
    {
        /** @var \Laravel\Socialite\Two\GoogleProvider $driver */
        $driver = Socialite::driver('google');
        // Windows local dev: PHP ships without a CA bundle, so cURL can't verify
        // Google's SSL cert. Safe to skip on localhost only.
        if (app()->environment('local')) {
            $driver->setHttpClient(new \GuzzleHttp\Client(['verify' => false]));
        }
        return $driver->stateless();
    }

    public function redirectToGoogle()
    {
        return $this->socialiteGoogle()->redirect();
    }

    public function handleGoogleCallback()
    {
        try {
            $googleUser = $this->socialiteGoogle()->user();
        } catch (\Exception $e) {
            \Log::error('Google OAuth callback failed (Socialite): ' . $e->getMessage());
            $frontend = env('FRONTEND_URL', 'http://localhost:3000');
            return redirect($frontend . '?auth_error=1');
        }

        try {
            $user = User::updateOrCreate(
                ['email' => $googleUser->getEmail()],
                [
                    'name'              => $googleUser->getName() ?? $googleUser->getEmail(),
                    'google_id'         => $googleUser->getId(),
                    'avatar'            => $googleUser->getAvatar(),
                    'email_verified_at' => now(),
                ]
            );

            // Auto-accept any pending shares for this Gmail
            ProjectShare::where('invited_email', $user->email)
                ->whereNull('user_id')
                ->update(['user_id' => $user->id, 'accepted_at' => now()]);

            $token    = $user->generateApiToken();
            $frontend = env('FRONTEND_URL', 'http://localhost:3000');

            return redirect($frontend . '?token=' . $token);
        } catch (\Exception $e) {
            \Log::error('Google OAuth callback failed (user creation): ' . $e->getMessage());
            $frontend = env('FRONTEND_URL', 'http://localhost:3000');
            return redirect($frontend . '?auth_error=1');
        }
    }

    public function me(Request $request)
    {
        $user = $request->user();
        if (! $user) {
            return response()->json(null);
        }

        return response()->json([
            'id'     => $user->id,
            'name'   => $user->name,
            'email'  => $user->email,
            'avatar' => $user->avatar,
        ]);
    }

    public function logout(Request $request)
    {
        $user = $request->user();
        if ($user) {
            $user->update(['api_token' => null]);
        }

        return response()->json(['message' => 'Logged out']);
    }
}
