<?php

namespace App\Http\Controllers\Admin;

use App\Models\ActivityLog;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class ActivityLogController extends Controller
{
    public function index(Request $request)
    {
        $title = 'activity log';

        if ($request->ajax()) {
            $logs = ActivityLog::with('user')->latest();

            return \Yajra\DataTables\DataTables::of($logs)
                ->addIndexColumn()
                ->addColumn('user', function (ActivityLog $log) {
                    return $log->user
                        ? '<span class="font-weight-600">' . e($log->user->name) . '</span>'
                        : '<span class="text-muted">System</span>';
                })
                ->addColumn('action', function (ActivityLog $log) {
                    $color = $log->action_color;
                    $icon  = $log->action_icon;
                    return '<span class="badge badge-' . $color . '"><i class="fas ' . $icon . ' mr-1"></i>' . ucfirst($log->action) . '</span>';
                })
                ->addColumn('description', function (ActivityLog $log) {
                    return e($log->description);
                })
                ->addColumn('ip_address', function (ActivityLog $log) {
                    return '<code>' . e($log->ip_address ?? '—') . '</code>';
                })
                ->addColumn('created_at', function (ActivityLog $log) {
                    return '<span title="' . $log->created_at->format('Y-m-d H:i:s') . '">'
                        . $log->created_at->diffForHumans()
                        . '</span>';
                })
                ->rawColumns(['user', 'action', 'ip_address', 'created_at'])
                ->make(true);
        }

        return view('admin.activity.index', compact('title'));
    }

    public function destroy(Request $request)
    {
        ActivityLog::truncate();
        return redirect()->route('activity.index')->with(notify('Activity log cleared'));
    }
}
