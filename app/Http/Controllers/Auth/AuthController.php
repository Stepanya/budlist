<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Mail\OtpCodeMail;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    /** Session key holding the id of an account that registered/logged-in but still owes an OTP. */
    private const PENDING_KEY = 'otp_pending_user_id';

    // ===================================================================== login

    public function showLogin()
    {
        return view('auth.login');
    }

    public function login(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required', 'string'],
        ]);

        $this->throttle($request);

        $user = User::where('email', $data['email'])->first();

        if (! $user || ! Hash::check($data['password'], $user->password)) {
            RateLimiter::hit($this->throttleKey($request));
            throw ValidationException::withMessages([
                'email' => 'These credentials do not match our records.',
            ]);
        }

        RateLimiter::clear($this->throttleKey($request));

        // Unverified accounts must pass the email OTP before they can sign in.
        if ($user->email_verified_at === null) {
            return $this->startOtp($request, $user);
        }

        $remember = $request->boolean('remember');
        Auth::login($user, $remember);
        $request->session()->regenerate();

        return redirect()->intended(route('app'));
    }

    // ================================================================== register

    public function showRegister()
    {
        return view('auth.register');
    }

    public function register(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', 'unique:users,email'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
        ]);

        $user = User::create([
            'name' => $data['name'],
            'email' => $data['email'],
            'password' => $data['password'], // hashed by the model cast
        ]);

        return $this->startOtp($request, $user);
    }

    // ================================================================ OTP verify

    public function showVerify(Request $request)
    {
        $user = $this->pendingUser($request);
        if (! $user) {
            return redirect()->route('login');
        }

        return view('auth.verify', ['email' => $user->email]);
    }

    public function verify(Request $request): RedirectResponse
    {
        $user = $this->pendingUser($request);
        if (! $user) {
            return redirect()->route('login');
        }

        $request->validate(['otp' => ['required', 'string']]);
        $this->throttle($request, 'otp');

        if (! $user->otpIsValid($request->input('otp'))) {
            RateLimiter::hit($this->throttleKey($request, 'otp'));
            throw ValidationException::withMessages([
                'otp' => 'That code is invalid or has expired.',
            ]);
        }

        RateLimiter::clear($this->throttleKey($request, 'otp'));

        $user->markVerified();

        $request->session()->forget(self::PENDING_KEY);
        Auth::login($user, true);          // verifying establishes a remembered session
        $request->session()->regenerate();

        return redirect()->route('app');
    }

    public function resend(Request $request): RedirectResponse
    {
        $user = $this->pendingUser($request);
        if (! $user) {
            return redirect()->route('login');
        }

        $this->throttle($request, 'resend', 3, 60);
        RateLimiter::hit($this->throttleKey($request, 'resend'), 60);

        $this->sendOtp($user);

        return back()->with('status', 'A new code is on its way.');
    }

    // ===================================================================== logout

    public function logout(Request $request): RedirectResponse
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login');
    }

    // ===================================================================== helpers

    private function startOtp(Request $request, User $user): RedirectResponse
    {
        $this->sendOtp($user);
        $request->session()->put(self::PENDING_KEY, $user->id);

        return redirect()->route('verify');
    }

    private function sendOtp(User $user): void
    {
        $code = $user->issueOtp();
        Mail::to($user->email)->send(new OtpCodeMail($code, $user->name));
    }

    private function pendingUser(Request $request): ?User
    {
        $id = $request->session()->get(self::PENDING_KEY);

        return $id ? User::find($id) : null;
    }

    private function throttleKey(Request $request, string $bucket = 'login'): string
    {
        return $bucket . '|' . $request->ip();
    }

    private function throttle(Request $request, string $bucket = 'login', int $max = 8, int $decay = 60): void
    {
        if (RateLimiter::tooManyAttempts($this->throttleKey($request, $bucket), $max)) {
            $seconds = RateLimiter::availableIn($this->throttleKey($request, $bucket));
            throw ValidationException::withMessages([
                ($bucket === 'login' ? 'email' : 'otp') => "Too many attempts. Try again in {$seconds}s.",
            ]);
        }
    }
}
