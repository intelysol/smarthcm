@extends('layouts.learning')

@section('title', 'Learning Content Player')

@section('content')
<div class="card">
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1rem;">
        <div>
            <h2>{{ $item->title }}</h2>
            <span class="badge badge-info">{{ $item->item_type }}</span>
        </div>
        <a href="{{ route('hcm.me.learning.dashboard') }}" class="btn btn-outline">Back to Dashboard</a>
    </div>

    <div style="background: #0f172a; color: #fff; padding: 2rem; border-radius: 0.375rem; min-height: 300px; display: flex; flex-direction: column; justify-content: center; align-items: center;">
        @if($item->content && $item->content->content_type === 'video')
            <div style="text-align: center;">
                <div style="font-size: 3rem; margin-bottom: 1rem;">🎬</div>
                <h3>Video Learning Module</h3>
                <p style="color: #94a3b8; margin-top: 0.5rem;">Simulated video player tracking watch duration and progress.</p>
            </div>
        @elseif($item->content && $item->content->content_type === 'document')
            <div style="text-align: center;">
                <div style="font-size: 3rem; margin-bottom: 1rem;">📄</div>
                <h3>Document & Presentation Viewer</h3>
                <p style="color: #94a3b8; margin-top: 0.5rem;">Document reference ID: {{ $item->content->document_id ?? 'Embedded File' }}</p>
            </div>
        @else
            <div style="text-align: center;">
                <div style="font-size: 3rem; margin-bottom: 1rem;">📚</div>
                <h3>Interactive Lesson</h3>
                <p style="color: #94a3b8; margin-top: 0.5rem;">{{ $item->content->body ?? 'Interactive learning content.' }}</p>
            </div>
        @endif
    </div>

    <div style="margin-top: 1.5rem; display: flex; justify-content: flex-end;">
        <button class="btn btn-primary" onclick="alert('Progress updated to 100%')">Mark as Complete</button>
    </div>
</div>
@endsection
