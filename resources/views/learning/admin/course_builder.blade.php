@extends('layouts.learning')

@section('title', 'Course Builder: ' . $course->title)

@section('content')
<div class="card">
    <div style="display: flex; justify-content: space-between; align-items: center; border-bottom: 1px solid var(--border); padding-bottom: 1rem; margin-bottom: 1.5rem;">
        <div>
            <h2>Course Builder: {{ $course->title }}</h2>
            <p style="color: var(--text-muted);">Version {{ $course->current_version }} • Status: <strong>{{ $course->status }}</strong></p>
        </div>
        <div style="display: flex; gap: 0.5rem;">
            <a href="{{ route('hcm.learning.courses') }}" class="btn btn-outline">Back to Courses</a>
            <button class="btn btn-primary" onclick="alert('Course published successfully.')">Publish Course</button>
        </div>
    </div>

    <div style="margin-bottom: 2rem;">
        <h3>Modules & Curriculum</h3>
        @if($course->modules->isEmpty())
            <p style="color: var(--text-muted); padding: 1rem 0;">No modules added yet. Add a module to begin organizing lessons and content items.</p>
        @else
            @foreach($course->modules as $modIndex => $module)
                <div style="border: 1px solid var(--border); border-radius: 0.375rem; padding: 1rem; margin-bottom: 1rem;">
                    <h4>Module {{ $modIndex + 1 }}: {{ $module->title }}</h4>
                    <p style="font-size: 0.875rem; color: var(--text-muted);">{{ $module->description }}</p>

                    <div style="margin-top: 1rem; padding-left: 1rem; border-left: 2px solid var(--primary);">
                        <h5>Lessons & Learning Items</h5>
                        @foreach($module->lessons as $lesson)
                            <div style="padding: 0.5rem 0; font-size: 0.875rem; display: flex; justify-content: space-between;">
                                <span>{{ $lesson->title }} ({{ $lesson->estimated_duration_minutes }} min)</span>
                                <span class="badge badge-info">Lesson</span>
                            </div>
                        @endforeach
                    </div>
                </div>
            @endforeach
        @endif
    </div>

    <div>
        <h3>Course Assessments</h3>
        @if($course->assessments->isEmpty())
            <p style="color: var(--text-muted); padding: 1rem 0;">No assessments or exams configured for this course.</p>
        @else
            <table>
                <thead>
                    <tr>
                        <th>Title</th>
                        <th>Type</th>
                        <th>Passing Score</th>
                        <th>Total Points</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($course->assessments as $assessment)
                        <tr>
                            <td><strong>{{ $assessment->title }}</strong></td>
                            <td><span class="badge badge-info">{{ $assessment->assessment_type }}</span></td>
                            <td>{{ $assessment->passing_percentage }}%</td>
                            <td>{{ $assessment->total_points }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        @endif
    </div>
</div>
@endsection
