@extends('layouts.learning')

@section('title', 'My Learning Dashboard')

@section('content')
<div style="margin-bottom: 2rem;">
    <h2>Welcome back, {{ $employee->first_name ?? 'Learner' }}</h2>
    <p style="color: var(--text-muted);">Track your active courses, mandatory compliance requirements, and transcripts.</p>
</div>

<div class="grid" style="grid-template-columns: repeat(4, 1fr); margin-bottom: 2rem;">
    <div class="card" style="text-align: center;">
        <div style="font-size: 2rem; font-weight: 700; color: var(--primary);">{{ $enrollments->where('status', 'in_progress')->count() }}</div>
        <div style="color: var(--text-muted); font-size: 0.875rem;">In Progress</div>
    </div>
    <div class="card" style="text-align: center;">
        <div style="font-size: 2rem; font-weight: 700; color: var(--success);">{{ $enrollments->where('status', 'completed')->count() }}</div>
        <div style="color: var(--text-muted); font-size: 0.875rem;">Completed Courses</div>
    </div>
    <div class="card" style="text-align: center;">
        <div style="font-size: 2rem; font-weight: 700; color: var(--warning);">{{ $mandatory->count() }}</div>
        <div style="color: var(--text-muted); font-size: 0.875rem;">Mandatory Trainings</div>
    </div>
    <div class="card" style="text-align: center;">
        <div style="font-size: 2rem; font-weight: 700; color: #8b5cf6;">{{ $certificates->count() }}</div>
        <div style="color: var(--text-muted); font-size: 0.875rem;">Certificates Earned</div>
    </div>
</div>

<div class="card">
    <h3>Active Enrollments</h3>
    @if($enrollments->isEmpty())
        <p style="color: var(--text-muted); padding: 1rem 0;">You have no active enrollments. Browse the <a href="{{ route('hcm.me.learning.catalog') }}">Course Catalog</a> to begin learning.</p>
    @else
        <div class="grid" style="margin-top: 1rem;">
            @foreach($enrollments as $enrollment)
                <div style="border: 1px solid var(--border); border-radius: 0.375rem; padding: 1rem;">
                    <h4>{{ $enrollment->course->title ?? 'Course' }}</h4>
                    <span class="badge badge-info">{{ $enrollment->course->delivery_type ?? 'self_paced' }}</span>
                    <div class="progress-bar">
                        <div class="progress-fill" style="width: {{ $enrollment->progress_percentage }}%;"></div>
                    </div>
                    <div style="font-size: 0.875rem; color: var(--text-muted); display: flex; justify-content: space-between;">
                        <span>Progress: {{ $enrollment->progress_percentage }}%</span>
                        <a href="{{ route('hcm.me.learning.course_detail', $enrollment->course_id) }}" class="btn btn-primary" style="padding: 0.25rem 0.5rem; font-size: 0.75rem;">Continue</a>
                    </div>
                </div>
            @endforeach
        </div>
    @endif
</div>

<div class="card">
    <h3>Mandatory & Compliance Training</h3>
    @if($mandatory->isEmpty())
        <p style="color: var(--text-muted); padding: 1rem 0;">All mandatory compliance training requirements are currently satisfied.</p>
    @else
        <table>
            <thead>
                <tr>
                    <th>Course Title</th>
                    <th>Status</th>
                    <th>Due Date</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                @foreach($mandatory as $item)
                    <tr>
                        <td><strong>{{ $item->course->title ?? 'Course' }}</strong></td>
                        <td><span class="badge {{ $item->status === 'overdue' ? 'badge-danger' : 'badge-warning' }}">{{ $item->status }}</span></td>
                        <td>{{ $item->due_at ? \Carbon\Carbon::parse($item->due_at)->toFormattedDateString() : 'N/A' }}</td>
                        <td><a href="{{ route('hcm.me.learning.course_detail', $item->course_id) }}" class="btn btn-outline" style="padding: 0.25rem 0.5rem; font-size: 0.75rem;">Launch</a></td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    @endif
</div>
@endsection
