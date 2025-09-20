<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Mail;
use App\Models\User;
use App\Mail\LoginCodeMail;
use Inertia\Inertia;
use Inertia\Response;

class AuthenticatedSessionController extends Controller
{
    /**
     * Show the login page.
     */
    public function create(Request $request): Response
    {
        return Inertia::render('auth/Login', [
            'canResetPassword' => Route::has('password.request'),
            'status' => $request->session()->get('status'),
        ]);
    }

    /**
     * Handle an incoming authentication request.
     */
    public function store(LoginRequest $request): RedirectResponse
    {
        if (!Auth::validate($request->only('email', 'password'))) {
            return back()->withErrors([
                'email' => __('auth.failed'),
            ]);
        }

        $user = User::where('email', $request->email)->first();
        $code = rand(100000, 999999);

        session([
            'login_user_id' => $user->id,
            'login_code' => $code,
            'login_code_expires' => now()->addMinutes(5),
        ]);

        Mail::to($user->email)->send(new LoginCodeMail($code));

        return redirect()->route('login.verify');    }

    /**
     * Show the verify code page.
     */
    public function verifyCodeForm(): Response
    {
        return Inertia::render('auth/VerifyCode');
    }

    /**
     * Handle the verification code submission.
     */
    public function verifyPostCodeForm(Request $request): RedirectResponse
    {
        $request->validate([
            'code' => ['required', 'numeric'],
        ]);

        if (
            session('login_code') == $request->code &&
            now()->lt(session('login_code_expires'))
        ) {
            Auth::loginUsingId(session('login_user_id'));
            $request->session()->regenerate();

            session()->forget(['login_user_id', 'login_code', 'login_code_expires']);

            return redirect()->intended(route('dashboard', absolute: false));
        }

        return back()->withErrors(['code' => 'Invalid or expired code.']);
    }

    /**
     * Destroy an authenticated session.
     */
    public function destroy(Request $request): RedirectResponse
    {
        Auth::guard('web')->logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect('/');
    }
}
