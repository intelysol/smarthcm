@extends('layouts.learning')

@section('title', 'Manage Training Sessions')

@section('content')
<div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 2rem;">
    <div>
        <h2>Training Sessions & Rosters</h2>
        <p style="color: var(--text-muted);">Schedule physical and virtual training events, track attendance, and manage capacities.</p>
    </div>
    <a href="{{ route('hcm.learning.dashboard') }}" class="btn btn-outline">Back to Admin</a>
</div>

<div class="card">
    <table>
        <thead>
            <tr>
                <th>Course</th>
                <th>Instructor</th>
                <th>Venue / Location</th>
                <th>Start Time</th>
                <th>Capacity</th>
                <th>Status</th>
            </tr>
        </thead>
        <tbody>
            @foreach($sessions as $session)
                <tr>
                    <td><strong>{{ $session->course->title ?? 'Course' }}</strong></td>
                    <td>{{ $session->instructor->name ?? 'TBD' }}</td>
                    <td>{{ $session->venue->name ?? 'Virtual' }}</td>
                    <td>{{ $session->start_datetime ? \Carbon\Carbon::parse($session->start_datetime)->toDayDateTimeString() : 'TBD' }}</td>
                    <td>{{ $session->capacity }} seats</td>
                    <td><span class="badge badge-info">{{ $session->status }}</span></td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <div style="margin-top: 1rem;">
        {{ $sessions->links() }}
    </div>
</div>
@endsection
