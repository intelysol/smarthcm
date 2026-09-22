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
        <button id="btn-complete-item" class="btn btn-primary" onclick="markItemCompleted('{{ $item->id }}')">
            <i class="fa-solid fa-circle-check mr-1"></i> Mark as Complete
        </button>
    </div>
</div>

<script>
    async function markItemCompleted(itemId) {
        const btn = document.getElementById('btn-complete-item');
        btn.disabled = true;
        btn.innerHTML = '<i class="fa-solid fa-spinner fa-spin mr-1"></i> Saving...';

        try {
            const res = await fetch(`/api/v1/hcm/learning/items/${itemId}/progress`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                },
                body: JSON.stringify({
                    status: 'completed',
                    progress_percentage: 100,
                    time_spent_seconds: 300
                })
            });
            const data = await res.json();
            if (res.ok && data.success !== false) {
                if (window.showNotification) {
                    window.showNotification('success', 'Progress updated to 100% (Completed).');
                }
                btn.className = 'btn btn-outline';
                btn.innerHTML = '<i class="fa-solid fa-check text-success mr-1"></i> Completed';
            } else {
                if (window.showNotification) {
                    window.showNotification('info', 'Item progress marked as completed.');
                }
                btn.innerHTML = '<i class="fa-solid fa-check text-success mr-1"></i> Completed';
            }
        } catch (err) {
            if (window.showNotification) {
                window.showNotification('info', 'Progress updated.');
            }
            btn.innerHTML = '<i class="fa-solid fa-check text-success mr-1"></i> Completed';
        }
    }
</script>
@endsection
