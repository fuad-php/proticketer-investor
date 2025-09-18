<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use App\Models\AuditLog;

class AuditLogMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        // Log audit trail for sensitive operations
        if ($this->shouldLog($request)) {
            $this->logAudit($request, $response);
        }

        return $response;
    }

    /**
     * Determine if the request should be logged
     */
    private function shouldLog(Request $request): bool
    {
        $sensitiveMethods = ['POST', 'PUT', 'PATCH', 'DELETE'];
        $sensitiveRoutes = [
            'orders', 'investments', 'transactions', 'approvals', 
            'investors', 'clients', 'inquiries', 'users'
        ];

        if (!in_array($request->method(), $sensitiveMethods)) {
            return false;
        }

        foreach ($sensitiveRoutes as $route) {
            if (str_contains($request->path(), $route)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Log the audit trail
     */
    private function logAudit(Request $request, Response $response): void
    {
        try {
            AuditLog::create([
                'user_id' => auth()->id(),
                'event' => $this->getEventName($request),
                'auditable_type' => $this->getAuditableType($request),
                'auditable_id' => $this->getAuditableId($request),
                'old_values' => $this->getOldValues($request),
                'new_values' => $this->getNewValues($request),
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent(),
                'url' => $request->fullUrl(),
            ]);
        } catch (\Exception $e) {
            // Log error but don't break the request
            \Log::error('Audit log failed: ' . $e->getMessage());
        }
    }

    /**
     * Get event name from request
     */
    private function getEventName(Request $request): string
    {
        $method = $request->method();
        $path = $request->path();

        if (str_contains($path, 'orders')) {
            return match($method) {
                'POST' => 'order_created',
                'PUT', 'PATCH' => 'order_updated',
                'DELETE' => 'order_deleted',
                default => 'order_accessed'
            };
        }

        if (str_contains($path, 'investments')) {
            return match($method) {
                'POST' => 'investment_created',
                'PUT', 'PATCH' => 'investment_updated',
                'DELETE' => 'investment_deleted',
                default => 'investment_accessed'
            };
        }

        if (str_contains($path, 'transactions')) {
            return match($method) {
                'POST' => 'transaction_created',
                'PUT', 'PATCH' => 'transaction_updated',
                'DELETE' => 'transaction_deleted',
                default => 'transaction_accessed'
            };
        }

        if (str_contains($path, 'approvals')) {
            return match($method) {
                'POST' => 'approval_action',
                default => 'approval_accessed'
            };
        }

        return 'unknown_event';
    }

    /**
     * Get auditable type from request
     */
    private function getAuditableType(Request $request): string
    {
        $path = $request->path();

        if (str_contains($path, 'orders')) return 'App\\Models\\Order';
        if (str_contains($path, 'investments')) return 'App\\Models\\Investment';
        if (str_contains($path, 'transactions')) return 'App\\Models\\Transaction';
        if (str_contains($path, 'approvals')) return 'App\\Models\\Approval';
        if (str_contains($path, 'investors')) return 'App\\Models\\Investor';
        if (str_contains($path, 'clients')) return 'App\\Models\\Client';
        if (str_contains($path, 'inquiries')) return 'App\\Models\\Inquiry';
        if (str_contains($path, 'users')) return 'App\\Models\\User';

        return 'unknown';
    }

    /**
     * Get auditable ID from request
     */
    private function getAuditableId(Request $request): ?int
    {
        $segments = $request->segments();
        
        // Try to find ID in URL segments
        foreach ($segments as $segment) {
            if (is_numeric($segment)) {
                return (int) $segment;
            }
        }

        return null;
    }

    /**
     * Get old values (for updates)
     */
    private function getOldValues(Request $request): ?array
    {
        // This would need to be implemented based on your specific needs
        // For now, return null as we don't have access to old values in middleware
        return null;
    }

    /**
     * Get new values from request
     */
    private function getNewValues(Request $request): ?array
    {
        if (in_array($request->method(), ['POST', 'PUT', 'PATCH'])) {
            return $request->except(['_token', '_method', 'password', 'password_confirmation']);
        }

        return null;
    }
}
