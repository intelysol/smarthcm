@extends('layouts.learning')

@section('title', 'Official Learning Transcript')

@section('content')
<div class="card">
    <div style="display: flex; justify-content: space-between; align-items: center; border-bottom: 1px solid var(--border); padding-bottom: 1rem; margin-bottom: 1.5rem;">
        <div>
            <h2>Official Learning Transcript</h2>
            <p style="color: var(--text-muted);">Employee: <strong>{{ $employee->first_name ?? '' }} {{ $employee->last_name ?? '' }}</strong> ({{ $employee->employee_number ?? '' }})</p>
        </div>
        <button class="btn btn-outline" onclick="window.print()">Print Transcript</button>
    </div>

    @if($records->isEmpty())
        <p style="color: var(--text-muted); padding: 1rem 0;">No completed learning records found on your transcript.</p>
    @else
        <table>
            <thead>
                <tr>
                    <th>Course Code & Title</th>
                    <th>Completion Date</th>
                    <th>Score</th>
                    <th>Credits Earned</th>
                    <th>Learning Hours</th>
                    <th>Certificate Number</th>
                </tr>
            </thead>
            <tbody>
                @foreach($records as $rec)
                    <tr>
                        <td>
                            <strong>{{ $rec->course->title ?? 'Course' }}</strong>
                            <div style="font-size: 0.75rem; color: var(--text-muted);">Code: {{ $rec->course->code ?? 'N/A' }}</div>
                        </td>
                        <td>{{ $rec->completion_date }}</td>
                        <td>{{ $rec->final_score !== null ? $rec->final_score . '%' : 'Pass' }}</td>
                        <td>{{ $rec->credits_awarded }}</td>
                        <td>{{ $rec->learning_hours }} hrs</td>
                        <td>
                            @if($rec->certificate)
                                <code style="background: #f1f5f9; padding: 0.2rem 0.4rem; border-radius: 0.25rem;">{{ $rec->certificate->certificate_number }}</code>
                            @else
                                <span style="color: var(--text-muted);">—</span>
                            @endif
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    @endif
</div>
@endsection
