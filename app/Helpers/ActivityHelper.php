<?php

use App\Models\ActivityLog;
use Illuminate\Database\Eloquent\Model;

if (!function_exists('activity_log')) {
    /**
     * Log a user action.
     *
     * @param string      $action      created|updated|deleted|sold|purchased
     * @param string      $description Human-readable sentence
     * @param Model|null  $subject     The model the action was performed on
     * @param array       $properties  Optional extra data (old/new values)
     */
    function activity_log(
        string $action,
        string $description,
        ?Model $subject = null,
        array $properties = []
    ): void {
        try {
            ActivityLog::create([
                'user_id'      => auth()->id(),
                'action'       => $action,
                'subject_type' => $subject ? get_class($subject) : null,
                'subject_id'   => $subject?->id,
                'description'  => $description,
                'properties'   => $properties ?: null,
                'ip_address'   => request()->ip(),
            ]);
        } catch (\Throwable) {
            // Never let logging break the request
        }
    }
}
