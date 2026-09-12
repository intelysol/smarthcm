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
            <button type="button" class="btn btn-primary" onclick="alert('Attempt submitted for grading.')">Submit Assessment</button>
        </div>
    </form>
</div>
@endsection
