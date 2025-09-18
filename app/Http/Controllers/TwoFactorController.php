<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use PragmaRX\Google2FALaravel\Support\Authenticator;
use PragmaRX\Google2FALaravel\Google2FA;
use Illuminate\Support\Str;

class TwoFactorController extends Controller
{
    protected $google2fa;

    public function __construct(Google2FA $google2fa)
    {
        $this->google2fa = $google2fa;
    }

    public function showSetupForm()
    {
        $user = Auth::user();
        
        if ($user->hasTwoFactorEnabled()) {
            return redirect()->route('profile.edit')->with('info', '2FA is already enabled.');
        }

        $secretKey = $this->google2fa->generateSecretKey();
        $qrCodeUrl = $this->google2fa->getQRCodeUrl(
            config('app.name'),
            $user->email,
            $secretKey
        );

        return view('auth.2fa-setup', compact('secretKey', 'qrCodeUrl'));
    }

    public function enable(Request $request)
    {
        $request->validate([
            'secret' => 'required|string',
            'code' => 'required|string|size:6',
        ]);

        $user = Auth::user();
        $secret = $request->secret;
        $code = $request->code;

        // Verify the code
        if (!$this->google2fa->verifyKey($secret, $code)) {
            return back()->withErrors(['code' => 'Invalid verification code.']);
        }

        // Generate backup codes
        $backupCodes = $user->generateBackupCodes();

        // Enable 2FA
        $user->update([
            'google2fa_secret' => $secret,
            'two_factor_enabled' => true,
            'google2fa_enabled_at' => now(),
            'backup_codes' => $backupCodes,
        ]);

        return view('auth.2fa-backup-codes', compact('backupCodes'))
            ->with('success', '2FA has been enabled successfully. Please save your backup codes.');
    }

    public function showDisableForm()
    {
        $user = Auth::user();
        
        if (!$user->hasTwoFactorEnabled()) {
            return redirect()->route('profile.edit')->with('info', '2FA is not enabled.');
        }

        return view('auth.2fa-disable');
    }

    public function disable(Request $request)
    {
        $request->validate([
            'password' => 'required|string',
            'code' => 'required|string|size:6',
        ]);

        $user = Auth::user();

        // Verify password
        if (!Hash::check($request->password, $user->password)) {
            return back()->withErrors(['password' => 'Invalid password.']);
        }

        // Verify 2FA code
        if (!$this->google2fa->verifyKey($user->google2fa_secret, $request->code)) {
            return back()->withErrors(['code' => 'Invalid verification code.']);
        }

        // Disable 2FA
        $user->update([
            'google2fa_secret' => null,
            'two_factor_enabled' => false,
            'google2fa_enabled_at' => null,
            'backup_codes' => null,
            'failed_2fa_attempts' => 0,
            '2fa_locked_until' => null,
        ]);

        return redirect()->route('profile.edit')->with('success', '2FA has been disabled successfully.');
    }

    public function showVerifyForm()
    {
        $user = Auth::user();
        
        if (!$user->hasTwoFactorEnabled()) {
            return redirect()->route('dashboard');
        }

        if ($user->is2FALocked()) {
            return view('auth.2fa-locked');
        }

        return view('auth.2fa-verify');
    }

    public function verify(Request $request)
    {
        $request->validate([
            'code' => 'required|string|size:6',
        ]);

        $user = Auth::user();
        $code = $request->code;

        // Check if user is locked
        if ($user->is2FALocked()) {
            return back()->withErrors(['code' => 'Account is temporarily locked due to too many failed attempts.']);
        }

        // Verify the code
        if ($this->google2fa->verifyKey($user->google2fa_secret, $code)) {
            // Reset failed attempts
            $user->update([
                'failed_2fa_attempts' => 0,
                'last_2fa_verified_at' => now(),
            ]);

            // Store 2FA verification in session
            session(['2fa_verified' => true]);

            return redirect()->intended(route('dashboard'));
        }

        // Increment failed attempts
        $failedAttempts = $user->failed_2fa_attempts + 1;
        $updateData = ['failed_2fa_attempts' => $failedAttempts];

        // Lock account after 5 failed attempts
        if ($failedAttempts >= 5) {
            $updateData['2fa_locked_until'] = now()->addMinutes(30);
        }

        $user->update($updateData);

        return back()->withErrors(['code' => 'Invalid verification code.']);
    }

    public function verifyWithBackupCode(Request $request)
    {
        $request->validate([
            'backup_code' => 'required|string|size:8',
        ]);

        $user = Auth::user();
        $backupCode = strtoupper($request->backup_code);

        // Check if user is locked
        if ($user->is2FALocked()) {
            return back()->withErrors(['backup_code' => 'Account is temporarily locked due to too many failed attempts.']);
        }

        // Verify backup code
        if ($user->useBackupCode($backupCode)) {
            // Reset failed attempts
            $user->update([
                'failed_2fa_attempts' => 0,
                'last_2fa_verified_at' => now(),
            ]);

            // Store 2FA verification in session
            session(['2fa_verified' => true]);

            return redirect()->intended(route('dashboard'));
        }

        // Increment failed attempts
        $failedAttempts = $user->failed_2fa_attempts + 1;
        $updateData = ['failed_2fa_attempts' => $failedAttempts];

        // Lock account after 5 failed attempts
        if ($failedAttempts >= 5) {
            $updateData['2fa_locked_until'] = now()->addMinutes(30);
        }

        $user->update($updateData);

        return back()->withErrors(['backup_code' => 'Invalid backup code.']);
    }

    public function regenerateBackupCodes(Request $request)
    {
        $request->validate([
            'password' => 'required|string',
        ]);

        $user = Auth::user();

        // Verify password
        if (!Hash::check($request->password, $user->password)) {
            return back()->withErrors(['password' => 'Invalid password.']);
        }

        // Generate new backup codes
        $backupCodes = $user->generateBackupCodes();
        $user->update(['backup_codes' => $backupCodes]);

        return view('auth.2fa-backup-codes', compact('backupCodes'))
            ->with('success', 'New backup codes have been generated. Please save them.');
    }
}
