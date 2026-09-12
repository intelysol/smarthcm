@extends('layouts.learning')

@section('title', 'Manage Nominations')

@section('content')
<div class="card">
    <div style="display: flex; justify-content: space-between; align-items: center; border-bottom: 1px solid var(--border); padding-bottom: 1rem; margin-bottom: 1.5rem;">
        <div>
            <h2>Course Nominations</h2>
            <p style="color: var(--text-muted);">Nominate subordinates for targeted development and compliance courses.</p>
        </div>
        <a href="{{ route('hcm.manager.learning.dashboard') }}" class="btn btn-outline">Back to Team Hub</a>
    </div>

    @if($nominations->isEmpty())
        <p style="color: var(--text-muted); padding: 1rem 0;">No nominations have been submitted yet.</p>
    @else
        <table>
            <thead>
                <tr>
                    <th>Employee</th>
                    <th>Course</th>
                    <th>Status</th>
                    <th>Reason</th>
                    <th>Date</th>
                </tr>
            </thead>
            <tbody>
                @foreach($nominations as $nom)
                    <tr>
                        <td><strong>{{ $nom->employee->first_name ?? '' }} {{ $nom->employee->last_name ?? '' }}</strong></td>
                        <td>{{ $nom->course->title ?? 'Course' }}</td>
                        <td><span class="badge {{ $nom->status === 'approved' ? 'badge-success' : 'badge-warning' }}">{{ $nom->status }}</span></td>
                        <td>{{ $nom->reason ?? '—' }}</td>
                        <td>{{ $nom->created_at->toFormattedDateString() }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    @endif
</div>
@endsection
