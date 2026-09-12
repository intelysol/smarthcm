@extends('layouts.learning')

@section('title', 'L&D Administration Dashboard')

@section('content')
<div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 2rem;">
    <div>
        <h2>L&D Administration & Operations</h2>
        <p style="color: var(--text-muted);">Enterprise Learning Management System control center.</p>
    </div>
    <div style="display: flex; gap: 0.5rem;">
        <a href="{{ route('hcm.learning.courses') }}" class="btn btn-primary">Manage Courses</a>
        <a href="{{ route('hcm.learning.sessions') }}" class="btn btn-outline">Sessions</a>
        <a href="{{ route('hcm.learning.compliance') }}" class="btn btn-outline">Compliance</a>
        <a href="{{ route('hcm.learning.reports') }}" class="btn btn-outline">Analytics & Reports</a>
    </div>
</div>

<div class="grid" style="grid-template-columns: repeat(3, 1fr); margin-bottom: 2rem;">
    <div class="card" style="text-align: center;">
        <div style="font-size: 2.5rem; font-weight: 700; color: var(--primary);">{{ $courseCount }}</div>
        <div style="color: var(--text-muted);">Published Courses</div>
    </div>
    <div class="card" style="text-align: center;">
        <div style="font-size: 2.5rem; font-weight: 700; color: var(--success);">{{ $enrollmentCount }}</div>
        <div style="color: var(--text-muted);">Total Enrollments</div>
    </div>
    <div class="card" style="text-align: center;">
        <div style="font-size: 2.5rem; font-weight: 700; color: #8b5cf6;">{{ $certCount }}</div>
        <div style="color: var(--text-muted);">Certificates Issued</div>
    </div>
</div>

<div class="card">
    <h3>Recently Added Courses</h3>
    <table>
        <thead>
            <tr>
                <th>Code</th>
                <th>Title</th>
                <th>Delivery Type</th>
                <th>Status</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            @foreach($recentCourses as $course)
                <tr>
                    <td><code>{{ $course->code }}</code></td>
                    <td><strong>{{ $course->title }}</strong></td>
                    <td><span class="badge badge-info">{{ $course->delivery_type }}</span></td>
                    <td><span class="badge badge-success">{{ $course->status }}</span></td>
                    <td>
                        <a href="{{ route('hcm.learning.course_builder', $course->id) }}" class="btn btn-outline" style="padding: 0.25rem 0.5rem; font-size: 0.75rem;">Builder</a>
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>
</div>
@endsection
