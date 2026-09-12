@extends('analytics.layout')

@section('title', 'AI People Analytics Assistant — Flow HCM')

@section('content')
<div class="space-y-6 max-w-4xl mx-auto">
    <div class="text-center pb-4">
        <div class="inline-flex p-3 bg-indigo-100 text-indigo-700 rounded-2xl mb-3 shadow-sm">
            <i class="fa-solid fa-wand-magic-sparkles text-2xl"></i>
        </div>
        <h1 class="text-2xl font-bold text-slate-900 tracking-tight">AI People Analytics Assistant</h1>
        <p class="text-sm text-slate-500 mt-1">Ask natural language questions about your workforce, turnover trends, and operational HR metrics.</p>
    </div>

    <!-- Interactive Prompt Box -->
    <div class="bg-white p-6 rounded-2xl border border-slate-200 shadow-sm space-y-4">
        <div>
            <label class="block text-xs font-bold uppercase text-slate-700 mb-2">Ask a Question</label>
            <div class="flex space-x-2">
                <input type="text" id="aiQueryInput" placeholder="e.g., Why did turnover increase this quarter? or What is our current active headcount?" class="flex-1 text-sm border border-slate-300 rounded-xl px-4 py-3 shadow-sm focus:ring-indigo-500 focus:border-indigo-500">
                <button id="askAiBtn" class="px-6 py-3 bg-indigo-600 hover:bg-indigo-700 text-white font-semibold text-sm rounded-xl shadow transition flex items-center">
                    <span>Ask AI</span>
                    <i class="fa-solid fa-arrow-right ml-2"></i>
                </button>
            </div>
        </div>

        <!-- Sample Questions -->
        <div class="pt-2">
            <span class="text-xs font-semibold text-slate-400 uppercase tracking-wider block mb-2">Example Inquiries:</span>
            <div class="flex flex-wrap gap-2 text-xs">
                <button onclick="setQuery('What is our total active headcount and FTE?')" class="px-3 py-1.5 bg-slate-100 hover:bg-slate-200 text-slate-700 rounded-lg transition">"What is our active headcount?"</button>
                <button onclick="setQuery('Show me our annualized turnover rate and voluntary exits.')" class="px-3 py-1.5 bg-slate-100 hover:bg-slate-200 text-slate-700 rounded-lg transition">"What is our turnover rate?"</button>
                <button onclick="setQuery('What is our attendance rate and overtime hours this month?')" class="px-3 py-1.5 bg-slate-100 hover:bg-slate-200 text-slate-700 rounded-lg transition">"Attendance rate & overtime?"</button>
            </div>
        </div>
    </div>

    <!-- AI Response Card (Initially Hidden) -->
    <div id="aiResponseCard" class="hidden bg-white p-6 rounded-2xl border border-indigo-200 shadow-sm space-y-4">
        <div class="flex items-center space-x-2 text-indigo-700 font-bold text-sm">
            <i class="fa-solid fa-robot"></i>
            <span>Analytical Summary & Explanation</span>
        </div>
        <div id="aiExplanationText" class="text-slate-800 text-sm leading-relaxed bg-indigo-50/50 p-4 rounded-xl border border-indigo-100">
            <!-- Dynamically populated -->
        </div>
        <div class="pt-2 border-t border-slate-100 flex items-center justify-between text-xs text-slate-400">
            <span id="aiCitedSources">Data Sources: Core HR Registry, Snapshots</span>
            <span class="italic">Advisory analysis only. Verified against query specs.</span>
        </div>
    </div>
</div>

<script>
function setQuery(text) {
    document.getElementById('aiQueryInput').value = text;
}

document.getElementById('askAiBtn').addEventListener('click', async function() {
    const query = document.getElementById('aiQueryInput').value.trim();
    if (!query) return;

    const btn = this;
    btn.disabled = true;
    btn.innerText = 'Analyzing...';

    try {
        const response = await fetch('{{ route("api.v1.analytics.hcm.ai.query") }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            },
            body: JSON.stringify({ query: query })
        });

        const data = await response.json();
        document.getElementById('aiExplanationText').innerText = data.ai_explanation || 'No summary available.';
        document.getElementById('aiCitedSources').innerText = 'Data Sources: ' + (data.data_sources_cited || []).join(', ');
        document.getElementById('aiResponseCard').classList.remove('hidden');
    } catch (err) {
        alert('Failed to process AI analytical inquiry.');
    } finally {
        btn.disabled = false;
        btn.innerHTML = '<span>Ask AI</span><i class="fa-solid fa-arrow-right ml-2"></i>';
    }
});
</script>
@endsection
