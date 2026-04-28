<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use Illuminate\Http\Request;

class AuditLogController extends Controller
{
    public function index(Request $request)
    {
        if (! $request->user()->isAdmin()) {
            abort(403, 'Only admin can view audit logs.');
        }

        $logs = AuditLog::query()
            ->with('user')
            ->latest()
            ->paginate(20);

        return response()->json($logs);
    }

    public function show(Request $request, AuditLog $auditLog)
    {
        if (! $request->user()->isAdmin()) {
            abort(403, 'Only admin can view audit logs.');
        }

        $auditLog->load('user');

        return response()->json([
            'data' => $auditLog,
        ]);
    }
}