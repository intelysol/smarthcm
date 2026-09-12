<?php

namespace App\Domains\Attendance\Http\Controllers;

use App\Domains\Attendance\Models\HolidayCalendar;
use App\Domains\Attendance\Models\WorkCalendar;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CalendarController extends Controller
{
    public function workCalendars(Request $request): JsonResponse
    {
        $user = $request->user();
        $calendars = WorkCalendar::query()
            ->where('tenant_id', $user->tenant_id)
            ->with(['days', 'exceptions'])
            ->get();

        return response()->json(['data' => $calendars]);
    }

    public function holidayCalendars(Request $request): JsonResponse
    {
        $user = $request->user();
        $calendars = HolidayCalendar::query()
            ->where('tenant_id', $user->tenant_id)
            ->with('holidays')
            ->get();

        return response()->json(['data' => $calendars]);
    }
}
