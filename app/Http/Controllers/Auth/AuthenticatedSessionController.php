<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;

class AuthenticatedSessionController extends Controller
{
    /**
     * Display the login view.
     */
    public function create(): View
    {
        return view('auth.login');
    }

    /**
     * Handle an incoming authentication request.
     */
    // public function store(LoginRequest $request): RedirectResponse
    // {
    //     $request->authenticate();
    //     $request->session()->regenerate();

    //     return redirect()->intended(route('dashboard', absolute: false));
    // }
    public function store(Request $request): RedirectResponse
    {
        Log::info('ログイン処理開始', $request->only('email'));

        $request->validate([
            'email' => 'required|email',
            'password' => 'required',
        ]);

        $user = \App\Models\User::where('email', $request->email)->first();

        if (! $user || ! Hash::check($request->password, $user->password)) {
            Log::warning('手動認証：ログイン失敗');
            Log::info('セッション接続:', ['conn' => config('session.connection')]);
            Log::info('SESSION_DRIVER', ['driver' => config('session.driver')]);
            Log::info('SESSION_CONNECTION', ['conn' => config('session.connection')]);

            return back()->withErrors([
                'email' => trans('auth.failed'),
            ]);
        }

        Auth::login($user);
        $request->session()->regenerate();

        Log::info('手動認証：ログイン成功', ['user_id' => $user->id]);
        Log::info('セッション接続:', ['conn' => config('session.connection')]);
        Log::info('SESSION_DRIVER', ['driver' => config('session.driver')]);
        Log::info('SESSION_CONNECTION', ['conn' => config('session.connection')]);

        return redirect()->intended('/dashboard');
    }
    /**
     * Destroy an authenticated session.
     */
    public function destroy(Request $request): RedirectResponse
    {
        if (Auth::check()) { // セッション切れでもログイン画面へ遷移
            Auth::guard('web')->logout();

            $request->session()->invalidate();
            $request->session()->regenerateToken();
        }

        return redirect()->route('login');
    }
}
