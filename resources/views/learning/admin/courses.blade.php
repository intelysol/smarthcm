@extends('layouts.learning')

@section('title', 'Manage Courses')

@section('content')
<div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 2rem;">
    <div>
        <h2>Courses Management</h2>
        <p style="color: var(--text-muted);">Author, publish, version, and configure learning courses.</p>
    </div>
    <a href="{{ route('hcm.learning.dashboard') }}" class="btn btn-outline">Back to Admin</a>
</div>

<div class="card">
    <table>
        <thead>
            <tr>
                <th>Code</th>
                <th>Title</th>
                <th>Category</th>
                <th>Type</th>
                <th>Version</th>
                <th>Status</th>
                <th>Action</th>
            </tr>
        </thead>
        <tbody>
            @foreach($courses as $course)
                <tr>
                    <td><code>{{ $course->code }}</code></td>
                    <td><strong>{{ $course->title }}</strong></td>
                    <td>{{ $course->category->name ?? 'General' }}</td>
                    <td><span class="badge badge-info">{{ $course->delivery_type }}</span></td>
                    <td>v{{ $course->current_version }}</td>
                    <td><span class="badge badge-success">{{ $course->status }}</span></td>
                    <td>
                        <a href="{{ route('hcm.learning.course_builder', $course->id) }}" class="btn btn-primary" style="padding: 0.25rem 0.5rem; font-size: 0.75rem;">Edit Curriculum</a>
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <div style="margin-top: 1rem;">
        {{ $courses->links() }}
    </div>
</div>
@endsection
