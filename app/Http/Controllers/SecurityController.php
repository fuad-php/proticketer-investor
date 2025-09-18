<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\AuditLog;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Validation\Rules\Password;

class SecurityController extends Controller
{
    public function __construct()
    {
        $this->middleware('permission:view audit logs|manage security');
    }

    /**
     * Show audit logs
     */
    public function auditLogs(Request $request)
    {
        $query = AuditLog::with('user');

        if ($request->filled('user_id')) {
            $query->where('user_id', $request->user_id);
        }

        if ($request->filled('event')) {
            $query->where('event', 'like', '%' . $request->event . '%');
        }

        if ($request->filled('from_date')) {
            $query->whereDate('created_at', '>=', $request->from_date);
        }

        if ($request->filled('to_date')) {
            $query->whereDate('created_at', '<=', $request->to_date);
        }

        $auditLogs = $query->latest()->paginate(50);
        $users = User::where('is_active', true)->get();

        return view('security.audit-logs', compact('auditLogs', 'users'));
    }

    /**
     * Show security dashboard
     */
    public function dashboard()
    {
        $stats = [
            'total_users' => User::count(),
            'active_users' => User::where('is_active', true)->count(),
            'inactive_users' => User::where('is_active', false)->count(),
            'recent_logins' => User::whereNotNull('last_login_at')
                ->where('last_login_at', '>=', now()->subDays(7))
                ->count(),
            'failed_logins' => $this->getFailedLoginAttempts(),
            'audit_events_today' => AuditLog::whereDate('created_at', today())->count(),
        ];

        $recentAuditLogs = AuditLog::with('user')
            ->latest()
            ->take(10)
            ->get();

        return view('security.dashboard', compact('stats', 'recentAuditLogs'));
    }

    /**
     * Show user security settings
     */
    public function userSecurity()
    {
        $users = User::with('roles')->paginate(20);
        
        return view('security.user-security', compact('users'));
    }

    /**
     * Update user security settings
     */
    public function updateUserSecurity(Request $request, User $user)
    {
        $request->validate([
            'is_active' => 'boolean',
            'password' => ['nullable', 'confirmed', Password::defaults()],
        ]);

        $user->update([
            'is_active' => $request->boolean('is_active'),
        ]);

        if ($request->filled('password')) {
            $user->update([
                'password' => Hash::make($request->password),
            ]);
        }

        return redirect()->route('security.user-security')
            ->with('success', 'User security settings updated successfully.');
    }

    /**
     * Show failed login attempts
     */
    public function failedLogins()
    {
        $failedLogins = $this->getFailedLoginAttempts();
        
        return view('security.failed-logins', compact('failedLogins'));
    }

    /**
     * Clear failed login attempts
     */
    public function clearFailedLogins(Request $request)
    {
        $key = 'login-attempts:' . $request->ip();
        RateLimiter::clear($key);

        return redirect()->route('security.failed-logins')
            ->with('success', 'Failed login attempts cleared.');
    }

    /**
     * Export audit logs
     */
    public function exportAuditLogs(Request $request)
    {
        $query = AuditLog::with('user');

        if ($request->filled('user_id')) {
            $query->where('user_id', $request->user_id);
        }

        if ($request->filled('from_date')) {
            $query->whereDate('created_at', '>=', $request->from_date);
        }

        if ($request->filled('to_date')) {
            $query->whereDate('created_at', '<=', $request->to_date);
        }

        $auditLogs = $query->latest()->get();

        $callback = function() use ($auditLogs) {
            $file = fopen('php://output', 'w');
            
            fputcsv($file, [
                'Date', 'User', 'Event', 'IP Address', 'User Agent', 'URL'
            ]);

            foreach ($auditLogs as $log) {
                fputcsv($file, [
                    $log->created_at->format('Y-m-d H:i:s'),
                    $log->user->name ?? 'System',
                    $log->event,
                    $log->ip_address,
                    $log->user_agent,
                    $log->url,
                ]);
            }
            
            fclose($file);
        };

        return response()->stream($callback, 200, [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="audit_logs_' . now()->format('Y-m-d') . '.csv"',
        ]);
    }

    /**
     * Get failed login attempts
     */
    private function getFailedLoginAttempts()
    {
        // This is a simplified implementation
        // In a real application, you might want to store failed login attempts in the database
        return [];
    }
}
