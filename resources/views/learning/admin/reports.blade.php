@extends('layouts.learning')

@section('title', 'Learning Analytics & Reports')

@section('content')
<div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 2rem;">
    <div>
        <h2>Learning Analytics & Standard Reports</h2>
        <p style="color: var(--text-muted);">Executive reporting and CSV/JSON exportable training insights.</p>
    </div>
    <a href="{{ route('hcm.learning.dashboard') }}" class="btn btn-outline">Back to Admin</a>
</div>

<div class="grid" style="grid-template-columns: repeat(2, 1fr);">
    <div class="card">
        <h3>Training Completion Report</h3>
        <p style="color: var(--text-muted); font-size: 0.875rem; margin: 0.5rem 0 1rem;">Complete audit log of completed courses, employee scores, learning hours, and certificates.</p>
        <a href="{{ url('/api/v1/hcm/learning/reports/completion') }}" target="_blank" class="btn btn-primary">Run Completion Report</a>
    </div>

    <div class="card">
        <h3>Compliance Status Report</h3>
        <p style="color: var(--text-muted); font-size: 0.875rem; margin: 0.5rem 0 1rem;">Detailed matrix of mandatory assignments, overdue training flags, and completion percentages.</p>
        <a href="{{ url('/api/v1/hcm/learning/reports/compliance') }}" target="_blank" class="btn btn-primary">Run Compliance Report</a>
    </div>

    <div class="card">
        <h3>Certifications & Expiry Report</h3>
        <p style="color: var(--text-muted); font-size: 0.875rem; margin: 0.5rem 0 1rem;">Active credentials, upcoming certificate expirations, renewal schedules, and verification codes.</p>
        <a href="{{ url('/api/v1/hcm/learning/reports/certifications') }}" target="_blank" class="btn btn-primary">Run Certifications Report</a>
    </div>

    <div class="card">
        <h3>Training Costs & Budget ROI</h3>
        <p style="color: var(--text-muted); font-size: 0.875rem; margin: 0.5rem 0 1rem;">Cost breakdown by vendor, instructor, venue, and employee training investment.</p>
        <a href="{{ url('/api/v1/hcm/learning/reports/cost') }}" target="_blank" class="btn btn-primary">Run Cost Report</a>
    </div>
</div>
@endsection
