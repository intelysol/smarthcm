@extends('self-service.layout')

@section('title', "Request: {$service->name}")

@section('content')
<div class="max-w-4xl mx-auto space-y-6">
    <div class="flex items-center space-x-3">
        <a href="{{ route('self-service.catalog.index') }}" class="text-slate-400 hover:text-white text-sm">&larr; Back to Catalog</a>
        <span class="text-slate-600">|</span>
        <span class="text-xs text-teal-400 font-mono">{{ $service->service_code }}</span>
    </div>

    <div class="bg-slate-900 border border-slate-800 rounded-xl p-6 shadow-sm">
        <div class="flex items-start justify-between">
            <div>
                <h1 class="text-2xl font-bold text-white">{{ $service->name }}</h1>
                <p class="text-sm text-slate-400 mt-1">{{ $service->description }}</p>
            </div>
            <div class="text-right">
                <span class="px-3 py-1 rounded-full text-xs font-semibold bg-teal-900/50 text-teal-300 border border-teal-700/50">
                    SLA: {{ round(($sla_policy?->resolution_time_minutes ?? 2880) / 1440, 1) }} Business Days
                </span>
            </div>
        </div>
    </div>

    <!-- Deflection Articles (If any) -->
    @if(!empty($deflections) && count($deflections) > 0)
    <div class="bg-slate-900/60 border border-amber-500/20 rounded-xl p-4">
        <div class="flex items-center space-x-2 text-amber-400 text-sm font-semibold mb-2">
            <i class="fa-solid fa-lightbulb"></i>
            <span>Before you submit, did you know?</span>
        </div>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
            @foreach($deflections as $art)
            <a href="{{ route('self-service.knowledge.show', $art) }}" target="_blank" class="p-3 bg-slate-850 hover:bg-slate-800 rounded-lg border border-slate-800 text-xs text-slate-300 transition flex items-center justify-between">
                <span class="font-medium text-white truncate max-w-xs">{{ $art->title }}</span>
                <span class="text-teal-400 ml-2">Read &rarr;</span>
            </a>
            @endforeach
        </div>
    </div>
    @endif

    <!-- Dynamic Request Form Wizard -->
    <div class="bg-slate-900 border border-slate-800 rounded-xl p-6 shadow-sm">
        <h2 class="text-lg font-bold text-white mb-4">Complete Request Details</h2>

        <form action="{{ route('api.self-service.requests.store') }}" method="POST" class="space-y-4">
            @csrf
            <input type="hidden" name="hr_service_definition_id" value="{{ $service->id }}">

            <div>
                <label class="block text-xs font-semibold text-slate-300 uppercase tracking-wider mb-1">Subject / Title</label>
                <input type="text" name="subject" value="{{ $service->name }}" class="w-full px-3 py-2 bg-slate-950 border border-slate-800 rounded-lg text-sm text-white focus:outline-none focus:border-teal-500" required>
            </div>

            <!-- Dynamic Schema Fields -->
            @foreach($form_schema as $field)
            <div>
                <label class="block text-xs font-semibold text-slate-300 uppercase tracking-wider mb-1">
                    {{ $field['label'] }}
                    @if(!empty($field['required'])) <span class="text-rose-400">*</span> @endif
                </label>

                @if(($field['type'] ?? 'text') === 'textarea')
                <textarea name="form_data[{{ $field['key'] }}]" rows="3" class="w-full px-3 py-2 bg-slate-950 border border-slate-800 rounded-lg text-sm text-white focus:outline-none focus:border-teal-500" {{ !empty($field['required']) ? 'required' : '' }}></textarea>
                @elseif(($field['type'] ?? 'text') === 'select')
                <select name="form_data[{{ $field['key'] }}]" class="w-full px-3 py-2 bg-slate-950 border border-slate-800 rounded-lg text-sm text-white focus:outline-none focus:border-teal-500" {{ !empty($field['required']) ? 'required' : '' }}>
                    <option value="">-- Please select --</option>
                    @foreach($field['options'] ?? [] as $opt)
                    <option value="{{ $opt }}">{{ $opt }}</option>
                    @endforeach
                </select>
                @elseif(($field['type'] ?? 'text') === 'date')
                <input type="date" name="form_data[{{ $field['key'] }}]" class="w-full px-3 py-2 bg-slate-950 border border-slate-800 rounded-lg text-sm text-white focus:outline-none focus:border-teal-500" {{ !empty($field['required']) ? 'required' : '' }}>
                @else
                <input type="text" name="form_data[{{ $field['key'] }}]" class="w-full px-3 py-2 bg-slate-950 border border-slate-800 rounded-lg text-sm text-white focus:outline-none focus:border-teal-500" {{ !empty($field['required']) ? 'required' : '' }}>
                @endif
            </div>
            @endforeach

            <div>
                <label class="block text-xs font-semibold text-slate-300 uppercase tracking-wider mb-1">Priority</label>
                <select name="priority" class="w-full px-3 py-2 bg-slate-950 border border-slate-800 rounded-lg text-sm text-white focus:outline-none focus:border-teal-500">
                    <option value="normal" selected>Normal (Standard SLA)</option>
                    <option value="low">Low (Non-urgent)</option>
                    <option value="high">High (Time sensitive)</option>
                    <option value="urgent">Urgent (Critical deadline)</option>
                </select>
            </div>

            <div class="pt-4 border-t border-slate-800 flex items-center justify-end space-x-3">
                <a href="{{ route('self-service.catalog.index') }}" class="px-4 py-2 border border-slate-800 hover:bg-slate-800 text-slate-300 rounded-lg text-sm font-medium transition">
                    Cancel
                </a>
                <button type="submit" class="px-5 py-2 bg-teal-600 hover:bg-teal-500 text-white rounded-lg text-sm font-medium shadow-lg shadow-teal-600/30 transition">
                    Submit Request &rarr;
                </button>
            </div>
        </form>
    </div>
</div>
@endsection
