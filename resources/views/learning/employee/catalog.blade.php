@extends('layouts.learning')

@section('title', 'Learning Catalog')

@section('content')
<div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 2rem;">
    <div>
        <h2>Learning Catalog</h2>
        <p style="color: var(--text-muted);">Explore courses, programs, and certifications available in the organization.</p>
    </div>
</div>

<div class="grid">
    @forelse($courses as $course)
        <div class="card" style="display: flex; flex-direction: column; justify-content: space-between;">
            <div>
                <div style="display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 0.5rem;">
                    <span class="badge badge-info">{{ $course->delivery_type }}</span>
                    <span class="badge badge-warning">{{ $course->difficulty }}</span>
                </div>
                <h3 style="font-size: 1.125rem; margin-bottom: 0.5rem;">{{ $course->title }}</h3>
                <p style="color: var(--text-muted); font-size: 0.875rem; margin-bottom: 1rem;">
                    {{ $course->short_description ?? Str::limit($course->description, 90) }}
                </p>
            </div>
            <div>
                <div style="font-size: 0.75rem; color: var(--text-muted); margin-bottom: 0.75rem; display: flex; justify-content: space-between;">
                    <span>⏱ {{ $course->duration }} {{ $course->duration_unit }}</span>
                    <span>⭐ {{ $course->credit_points }} Credits</span>
                </div>
                <a href="{{ route('hcm.me.learning.course_detail', $course->id) }}" class="btn btn-primary" style="width: 100%; text-align: center;">View Details</a>
            </div>
        </div>
    @empty
        <div class="card" style="grid-column: 1 / -1; text-align: center; color: var(--text-muted);">
            No published courses available at this time.
        </div>
    @endforelse
</div>

<div style="margin-top: 1.5rem;">
    {{ $courses->links() }}
</div>
@endsection
