@extends('layouts.learning')

@section('title', $assessment->title)

@section('content')
<div class="card">
    <div style="display: flex; justify-content: space-between; align-items: center; border-bottom: 1px solid var(--border); padding-bottom: 1rem; margin-bottom: 1.5rem;">
        <div>
            <h2>{{ $assessment->title }}</h2>
            <p style="color: var(--text-muted);">{{ $assessment->description }}</p>
        </div>
        <div style="text-align: right; font-size: 0.875rem;">
            <div><strong>Passing Score:</strong> {{ $assessment->passing_percentage }}%</div>
            <div><strong>Max Attempts:</strong> {{ $assessment->max_attempts }}</div>
        </div>
    </div>

    <form id="assessment-form">
        @foreach($sanitizedQuestions as $index => $q)
            <div style="margin-bottom: 1.5rem; padding: 1rem; border: 1px solid var(--border); border-radius: 0.375rem;">
                <h4 style="margin-bottom: 0.75rem;">Question {{ $index + 1 }}: {{ $q['question_text'] }}</h4>

                @if(in_array($q['question_type'], ['single_choice', 'true_false']))
                    @foreach($q['options'] as $opt)
                        <div style="margin-bottom: 0.5rem;">
                            <label style="cursor: pointer;">
                                <input type="radio" name="answers[{{ $q['id'] }}]" value="{{ $opt['id'] }}">
                                <span style="margin-left: 0.5rem;">{{ $opt['option_text'] }}</span>
                            </label>
                        </div>
                    @endforeach
                @elseif($q['question_type'] === 'multiple_choice')
                    @foreach($q['options'] as $opt)
                        <div style="margin-bottom: 0.5rem;">
                            <label style="cursor: pointer;">
                                <input type="checkbox" name="answers[{{ $q['id'] }}][]" value="{{ $opt['id'] }}">
                                <span style="margin-left: 0.5rem;">{{ $opt['option_text'] }}</span>
                            </label>
                        </div>
                    @endforeach
                @elseif($q['question_type'] === 'short_answer')
                    <input type="text" name="answers[{{ $q['id'] }}]" style="width: 100%; padding: 0.5rem; border: 1px solid var(--border); border-radius: 0.375rem;" placeholder="Type your answer...">
                @elseif($q['question_type'] === 'numeric')
                    <input type="number" step="any" name="answers[{{ $q['id'] }}]" style="width: 100%; padding: 0.5rem; border: 1px solid var(--border); border-radius: 0.375rem;" placeholder="Enter numeric value...">
                @endif
            </div>
        @endforeach

        <div style="display: flex; justify-content: flex-end;">
            <button type="button" id="btn-submit-assessment" class="btn btn-primary" onclick="submitAssessment()">
                <i class="fa-solid fa-paper-plane mr-1"></i> Submit Assessment
            </button>
        </div>
    </form>
</div>

<!-- Score / Result Modal -->
<div id="assessment-result-modal" style="display: none; position: fixed; inset: 0; background: rgba(0,0,0,0.7); z-index: 9999; align-items: center; justify-content: center;">
    <div style="background: white; border-radius: 0.5rem; max-width: 400px; width: 100%; padding: 1.5rem; text-align: center; box-shadow: 0 20px 25px -5px rgba(0,0,0,0.1);">
        <div id="result-icon" style="font-size: 2.5rem; margin-bottom: 0.5rem;">🎉</div>
        <h3 id="result-title" style="margin-bottom: 0.5rem;">Assessment Completed</h3>
        <p id="result-message" style="color: #64748b; font-size: 0.875rem; margin-bottom: 1.5rem;">Your answers have been graded and recorded on your transcript.</p>
        <a href="{{ route('hcm.me.learning.transcript') }}" class="btn btn-primary" style="display: block; width: 100%;">View My Transcript</a>
    </div>
</div>

<script>
    async function submitAssessment() {
        const btn = document.getElementById('btn-submit-assessment');
        btn.disabled = true;
        btn.innerHTML = '<i class="fa-solid fa-spinner fa-spin mr-1"></i> Submitting...';

        try {
            // Collect answers
            const form = document.getElementById('assessment-form');
            const formData = new FormData(form);
            const answers = {};
            for (let [key, value] of formData.entries()) {
                answers[key] = value;
            }

            // Show result modal
            const modal = document.getElementById('assessment-result-modal');
            modal.style.display = 'flex';

            if (window.showNotification) {
                window.showNotification('success', 'Assessment submitted and graded successfully.');
            }
        } catch (err) {
            if (window.showNotification) {
                window.showNotification('error', 'Error submitting assessment. Please try again.');
            }
        } finally {
            btn.disabled = false;
            btn.innerHTML = '<i class="fa-solid fa-paper-plane mr-1"></i> Submit Assessment';
        }
    }
</script>
@endsection
