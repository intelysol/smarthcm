@extends('layouts.learning')

@section('title', 'Compliance Matrix')

@section('content')
<div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 2rem;">
    <div>
        <h2>Compliance Training Status Matrix</h2>
        <p style="color: var(--text-muted);">Real-time compliance tracking across employees and organizational departments.</p>
    </div>
    <a href="{{ route('hcm.learning.requirements') }}" class="btn btn-outline">Back to Rules</a>
</div>

<div class="card">
    <table>
        <thead>
            <tr>
                <th>Employee</th>
                <th>Required Course</th>
                <th>Assigned Date</th>
                <th>Due Date</th>
                <th>Compliance Status</th>
            </tr>
        </thead>
        <tbody>
            @foreach($assignments as $assignment)
                <tr>
                    <td><strong>{{ $assignment->employee->first_name ?? '' }} {{ $assignment->employee->last_name ?? '' }}</strong></td>
                    <td>{{ $assignment->course->title ?? 'Course' }}</td>
                    <td>{{ $assignment->assigned_at ? \Carbon\Carbon::parse($assignment->assigned_at)->toDateString() : 'N/A' }}</td>
                    <td>{{ $assignment->due_at ? \Carbon\Carbon::parse($assignment->due_at)->toDateString() : 'N/A' }}</td>
                    <td>
                        <span class="badge {{ $assignment->status === 'completed' ? 'badge-success' : ($assignment->status === 'overdue' ? 'badge-danger' : 'badge-warning') }}">
                            {{ $assignment->status }}
                        </span>
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <div style="margin-top: 1rem;">
        {{ $assignments->links() }}
    </div>
</div>
@endsection
