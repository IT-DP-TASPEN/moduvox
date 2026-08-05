<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Date;
use Symfony\Component\HttpFoundation\Response;

class UseBusinessDate
{
    public function handle(Request $request, Closure $next): Response
    {
        $businessDate = config('app.business_date');
        $usesBusinessDate = $businessDate && Auth::check();

        if ($usesBusinessDate) {
            Date::setTestNow(function () use ($businessDate) {
                $timezone = config('app.timezone');
                $time = (new \DateTimeImmutable('now', new \DateTimeZone($timezone)))->format('H:i:s.u');

                return Carbon::parse($businessDate, $timezone)->setTimeFromTimeString($time);
            });
        }

        $response = $next($request);

        if ($usesBusinessDate) {
            Date::setTestNow();
        }

        return $response;
    }
}
