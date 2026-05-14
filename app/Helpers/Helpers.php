<?php

use Illuminate\Support\Facades\Request;

if (!function_exists('route_is')) {
    function route_is($route): bool
    {
        if (is_array($route)) {
            foreach ($route as $r) {
                if (Request::routeIs($r)) {
                    return true;
                }
            }
            return false;
        }
        return (bool) Request::routeIs($route);
    }
}

if (!function_exists('notify')) {
    function notify(string $message, string $type = 'success'): array
    {
        return [
            'message'    => $message,
            'alert-type' => $type,
        ];
    }
}
