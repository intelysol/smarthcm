@extends('layouts.learning')

@section('title', 'Manage Enrollments')

@section('content')
<div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 2rem;">
    <div>
        <h2>Enrollments Roster</h2>
        <p style="color: var(--text-muted);">Manage employee enrollments, approval workflows, and participation.</p>
    </div>
    <a href="{{ route('hcm.learning.dashboard') }}" class="btn btn-outline">Back to Admin</a>
</div>

<div class="card">
    <table>
        <thead>
            <tr>
                <th>Employee</th>
                <th>Course</th>
                <th>Enrollment Type</th>
                <th>Progress</th>
                <th>Status</th>
            </tr>
        </thead>
        <tbody>
            @foreach($enrollments as $enrollment)
                <tr>
                    <td><strong>{{ $enrollment->employee->first_name ?? '' }} {{ $enrollment->employee->last_name ?? '' }}</strong></td>
                    <td>{{ $enrollment->course->title ?? 'Course' }}</td>
                    <td><span class="badge badge-info">{{ $enrollment->enrollment_type }}</span></td>
                    <td>
                        <div class="progress-bar" style="width: 120px;">
                            <div class="progress-fill" style="width: {{ $enrollment->progress_percentage }}%;"></div>
                        </div>
                        <span style="font-size: 0.75rem;">{{ $enrollment->progress_percentage }}%</span>
                    </td>
                    <td><span class="badge badge-success">{{ $enrollment->status }}</span></td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <div style="margin-top: 1rem;">
        {{ $enrollments->links() }}
    </div>
</div>
@endsection
