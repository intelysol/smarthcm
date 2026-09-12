@extends('layouts.learning')

@section('title', 'Mandatory Training Rules')

@section('content')
<div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 2rem;">
    <div>
        <h2>Mandatory Training & Compliance Requirements</h2>
        <p style="color: var(--text-muted);">Configure automatic compliance assignment rules triggered by employee lifecycle events.</p>
    </div>
    <a href="{{ route('hcm.learning.compliance') }}" class="btn btn-primary">View Compliance Matrix</a>
</div>

<div class="card">
    <table>
        <thead>
            <tr>
                <th>Requirement Title</th>
                <th>Course</th>
                <th>Target Scope</th>
                <th>Deadline Rule</th>
                <th>Status</th>
            </tr>
        </thead>
        <tbody>
            @foreach($requirements as $req)
                <tr>
                    <td><strong>{{ $req->title }}</strong></td>
                    <td>{{ $req->course->title ?? 'Course' }}</td>
                    <td><span class="badge badge-info">{{ $req->target_type }}</span></td>
                    <td>{{ $req->deadline_type }}</td>
                    <td><span class="badge badge-success">{{ $req->status }}</span></td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <div style="margin-top: 1rem;">
        {{ $requirements->links() }}
    </div>
</div>
@endsection
