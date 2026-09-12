@extends('layouts.learning')

@section('title', $course->title)

@section('content')
<div style="display: flex; gap: 2rem; align-items: flex-start;">
    <div style="flex: 2;">
        <div class="card">
            <div style="display: flex; gap: 0.5rem; margin-bottom: 0.5rem;">
                <span class="badge badge-info">{{ $course->delivery_type }}</span>
                <span class="badge badge-warning">{{ $course->difficulty }}</span>
                <span class="badge badge-success">{{ $course->language }}</span>
            </div>
            <h2>{{ $course->title }}</h2>
            <p style="color: var(--text-muted); margin-top: 1rem;">{{ $course->description }}</p>

            @if($course->objectives->isNotEmpty())
                <div style="margin-top: 2rem;">
                    <h3>Learning Objectives</h3>
                    <ul style="margin-top: 0.5rem; padding-left: 1.5rem;">
                        @foreach($course->objectives as $obj)
                            <li style="margin-bottom: 0.25rem;"><strong>{{ $obj->objective }}</strong>: {{ $obj->description }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            @if($course->modules->isNotEmpty())
                <div style="margin-top: 2rem;">
                    <h3>Curriculum & Modules</h3>
                    <div style="margin-top: 1rem;">
                        @foreach($course->modules as $index => $module)
                            <div style="border: 1px solid var(--border); border-radius: 0.375rem; padding: 1rem; margin-bottom: 0.75rem;">
                                <h4>Module {{ $index + 1 }}: {{ $module->title }}</h4>
                                <p style="font-size: 0.875rem; color: var(--text-muted);">{{ $module->description }}</p>
                                @if($module->lessons->isNotEmpty())
                                    <ul style="margin-top: 0.5rem; padding-left: 1.25rem; font-size: 0.875rem;">
                                        @foreach($module->lessons as $lesson)
                                            <li>{{ $lesson->title }} ({{ $lesson->estimated_duration_minutes }} min)</li>
                                        @endforeach
                                    </ul>
                                @endif
                            </div>
                        @endforeach
                    </div>
                </div>
            @endif
        </div>
    </div>

    <div style="flex: 1;">
        <div class="card">
            <h3>Course Overview</h3>
            <div style="margin: 1rem 0; font-size: 0.875rem;">
                <div style="display: flex; justify-content: space-between; padding: 0.5rem 0; border-bottom: 1px solid var(--border);">
                    <span>Duration:</span>
                    <strong>{{ $course->duration }} {{ $course->duration_unit }}</strong>
                </div>
                <div style="display: flex; justify-content: space-between; padding: 0.5rem 0; border-bottom: 1px solid var(--border);">
                    <span>Credit Points:</span>
                    <strong>{{ $course->credit_points }}</strong>
                </div>
                <div style="display: flex; justify-content: space-between; padding: 0.5rem 0; border-bottom: 1px solid var(--border);">
                    <span>Passing Score:</span>
                    <strong>{{ $course->passing_score ?? 'N/A' }}%</strong>
                </div>
                <div style="display: flex; justify-content: space-between; padding: 0.5rem 0;">
                    <span>Provider:</span>
                    <strong>{{ $course->provider->name ?? 'Internal' }}</strong>
                </div>
            </div>

            <form action="{{ url('/api/v1/hcm/me/learning/courses/' . $course->id . '/enroll') }}" method="POST">
                @csrf
                <button type="submit" class="btn btn-primary" style="width: 100%;">Enroll Now</button>
            </form>
        </div>
    </div>
</div>
@endsection
