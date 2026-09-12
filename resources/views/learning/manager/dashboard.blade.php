@extends('layouts.learning')

@section('title', 'Manager Learning Hub')

@section('content')
<div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 2rem;">
    <div>
        <h2>Manager Learning Hub</h2>
        <p style="color: var(--text-muted);">Oversee team training compliance, skill development, and course nominations.</p>
    </div>
    <a href="{{ route('hcm.manager.learning.nominations') }}" class="btn btn-primary">Nominate Team Member</a>
</div>

<div class="card">
    <h3>Direct Reports Learning Status</h3>
    @if($team->isEmpty())
        <p style="color: var(--text-muted); padding: 1rem 0;">No direct reports found assigned to your management hierarchy.</p>
    @else
        <table>
            <thead>
                <tr>
                    <th>Employee</th>
                    <th>Active Enrollments</th>
                    <th>Progress</th>
                    <th>Status</th>
                </tr>
            </thead>
            <tbody>
                @foreach($team as $member)
                    <tr>
                        <td>
                            <strong>{{ $member->first_name }} {{ $member->last_name }}</strong>
                            <div style="font-size: 0.75rem; color: var(--text-muted);">{{ $member->employee_number }}</div>
                        </td>
                        <td>
                            {{ $member->learningEnrollments->count() }} active courses
                        </td>
                        <td>
                            @php $avgProgress = $member->learningEnrollments->avg('progress_percentage') ?? 0; @endphp
                            <div class="progress-bar" style="width: 150px;">
                                <div class="progress-fill" style="width: {{ $avgProgress }}%;"></div>
                            </div>
                            <span style="font-size: 0.75rem; color: var(--text-muted);">{{ round($avgProgress, 1) }}% avg</span>
                        </td>
                        <td>
                            <span class="badge badge-success">Active</span>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    @endif
</div>
@endsection
