<?php

namespace App\Domains\Mobility\Http\Controllers;

use App\Domains\Mobility\Models\MobilityAssignment;
use App\Domains\Mobility\Models\MobilityBusinessTraveler;
use App\Domains\Mobility\Models\MobilityProgram;
use App\Domains\Mobility\Models\MobilityRelocationCase;
use App\Domains\Mobility\Models\MobilityRequest;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\View\View;

class MobilityDashboardController extends Controller
{
    public function index(Request $request): View
    {
        $tenantId = $request->user()?->tenant_id;

        $totalPrograms = MobilityProgram::when($tenantId, fn ($q) => $q->where('tenant_id', $tenantId))->count();
        $activeAssignments = MobilityAssignment::when($tenantId, fn ($q) => $q->where('tenant_id', $tenantId))->where('status', 'active')->count();
        $pendingRequests = MobilityRequest::when($tenantId, fn ($q) => $q->where('tenant_id', $tenantId))->where('status', 'submitted')->count();
        $activeRelocations = MobilityRelocationCase::when($tenantId, fn ($q) => $q->where('tenant_id', $tenantId))->where('status', '!=', 'completed')->count();
        $activeTravelers = MobilityBusinessTraveler::when($tenantId, fn ($q) => $q->where('tenant_id', $tenantId))->where('status', 'registered')->count();

        $recentAssignments = MobilityAssignment::when($tenantId, fn ($q) => $q->where('tenant_id', $tenantId))
            ->with(['employee'])
            ->latest()
            ->take(5)
            ->get();

        $recentRequests = MobilityRequest::when($tenantId, fn ($q) => $q->where('tenant_id', $tenantId))
            ->with(['employee'])
            ->latest()
            ->take(5)
            ->get();

        return view('mobility.dashboard', compact(
            'totalPrograms',
            'activeAssignments',
            'pendingRequests',
            'activeRelocations',
            'activeTravelers',
            'recentAssignments',
            'recentRequests'
        ));
    }

    public function programs(): View
    {
        $programs = MobilityProgram::with('policyVersions')->latest()->paginate(15);
        return view('mobility.programs.index', compact('programs'));
    }

    public function requests(): View
    {
        $requests = MobilityRequest::with(['employee', 'program'])->latest()->paginate(15);
        return view('mobility.requests.index', compact('requests'));
    }

    public function assignments(): View
    {
        $assignments = MobilityAssignment::with(['employee', 'relocationCase'])->latest()->paginate(15);
        return view('mobility.assignments.index', compact('assignments'));
    }

    public function relocation(): View
    {
        $cases = MobilityRelocationCase::with(['assignment.employee', 'items'])->latest()->paginate(15);
        return view('mobility.relocation.index', compact('cases'));
    }

    public function businessTravelers(): View
    {
        $travelers = MobilityBusinessTraveler::with(['employee'])->latest()->paginate(15);
        return view('mobility.travelers.index', compact('travelers'));
    }
}
