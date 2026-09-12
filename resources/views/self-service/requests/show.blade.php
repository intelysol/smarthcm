@extends('self-service.layout')

@section('title', "Request {$request->request_number}")

@section('content')
<div class="space-y-6">
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
        <div>
            <div class="flex items-center space-x-3">
                <a href="{{ route('self-service.requests.index') }}" class="text-slate-400 hover:text-white text-sm">&larr; Back to Requests</a>
                <span class="text-slate-600">|</span>
                <span class="font-mono text-teal-400 font-bold">{{ $request->request_number }}</span>
            </div>
            <h1 class="text-2xl font-bold text-white mt-1">{{ $request->subject }}</h1>
            <div class="text-xs text-slate-400 mt-1">Submitted on {{ $request->created_at?->format('F d, Y H:i') }} &bull; Service: {{ $request->service?->name }}</div>
        </div>
        <div class="flex items-center space-x-3">
            <span class="px-3 py-1 rounded-full text-xs font-semibold bg-teal-900/60 text-teal-300 border border-teal-700/50 uppercase tracking-wider">
                {{ ucfirst(str_replace('_', ' ', $request->status)) }}
            </span>
        </div>
    </div>

    <!-- Main Grid: Conversation & Metadata Sidebar -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Conversation & Activity Timeline (2 Cols) -->
        <div class="lg:col-span-2 space-y-6">
            <!-- Dynamic Form Data Card -->
            @if(!empty($request->form_data) && count($request->form_data) > 0)
            <div class="bg-slate-900 border border-slate-800 rounded-xl p-5 shadow-sm">
                <h2 class="text-base font-bold text-white mb-3">Submitted Request Details</h2>
                <dl class="grid grid-cols-1 sm:grid-cols-2 gap-3 text-xs">
                    @foreach($request->form_data as $key => $val)
                    <div class="bg-slate-950 p-3 rounded-lg border border-slate-850">
                        <dt class="text-slate-400 font-medium uppercase tracking-wider">{{ ucfirst(str_replace('_', ' ', $key)) }}</dt>
                        <dd class="text-white font-semibold mt-1">{{ is_array($val) ? json_encode($val) : $val }}</dd>
                    </div>
                    @endforeach
                </dl>
            </div>
            @endif

            <!-- Conversation Thread -->
            <div class="bg-slate-900 border border-slate-800 rounded-xl p-5 shadow-sm space-y-4">
                <h2 class="text-base font-bold text-white">Conversation &amp; Updates</h2>

                <div class="space-y-3">
                    @forelse($comments as $cmt)
                    <div class="p-4 rounded-xl {{ $cmt->comment_type === 'internal' ? 'bg-amber-950/30 border border-amber-800/40' : 'bg-slate-950 border border-slate-850' }}">
                        <div class="flex items-center justify-between text-xs mb-2">
                            <div class="flex items-center space-x-2">
                                <span class="font-bold text-white">{{ $cmt->user?->name ?? 'HR Agent' }}</span>
                                @if($cmt->comment_type === 'internal')
                                <span class="px-2 py-0.5 rounded text-[10px] font-bold bg-amber-900/60 text-amber-300 border border-amber-700/50 uppercase">Internal Note</span>
                                @endif
                            </div>
                            <span class="text-slate-500">{{ $cmt->created_at?->diffForHumans() }}</span>
                        </div>
                        <p class="text-sm text-slate-300 whitespace-pre-line">{{ $cmt->message }}</p>
                    </div>
                    @empty
                    <div class="text-center py-6 text-sm text-slate-500">No communication messages yet.</div>
                    @endforelse
                </div>

                <!-- Add Comment Box -->
                <form action="{{ route('api.self-service.requests.comments.store', $request) }}" method="POST" class="pt-4 border-t border-slate-800 space-y-3">
                    @csrf
                    <textarea name="message" rows="3" placeholder="Write a response to HR..." class="w-full px-3 py-2 bg-slate-950 border border-slate-800 rounded-lg text-sm text-white focus:outline-none focus:border-teal-500" required></textarea>
                    <div class="flex justify-end">
                        <button type="submit" class="px-4 py-2 bg-teal-600 hover:bg-teal-500 text-white rounded-lg text-xs font-semibold shadow-sm transition">
                            Send Message
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Request Summary Sidebar (1 Col) -->
        <div class="space-y-6">
            <div class="bg-slate-900 border border-slate-800 rounded-xl p-5 shadow-sm space-y-4">
                <h3 class="text-sm font-bold text-white uppercase tracking-wider">Ticket Info</h3>

                <div class="space-y-3 text-xs">
                    <div>
                        <span class="text-slate-400 block">Assigned Queue</span>
                        <span class="text-white font-medium">{{ $request->assignedQueue?->name ?? 'Unassigned' }}</span>
                    </div>
                    <div>
                        <span class="text-slate-400 block">Assigned Specialist</span>
                        <span class="text-white font-medium">{{ $request->assignedUser?->name ?? 'Pending Assignment' }}</span>
                    </div>
                    <div>
                        <span class="text-slate-400 block">SLA Target Resolution</span>
                        <span class="text-teal-400 font-mono font-bold">{{ $request->due_at?->format('Y-m-d H:i') ?? 'Standard' }}</span>
                    </div>
                    <div>
                        <span class="text-slate-400 block">Priority</span>
                        <span class="text-white font-medium capitalize">{{ $request->priority }}</span>
                    </div>
                </div>
            </div>

            <!-- Generated Certificates (If any) -->
            @if($request->generatedDocuments->count() > 0)
            <div class="bg-slate-900 border border-slate-800 rounded-xl p-5 shadow-sm space-y-3">
                <h3 class="text-sm font-bold text-white uppercase tracking-wider">Generated Certificate</h3>
                @foreach($request->generatedDocuments as $doc)
                <div class="p-3 bg-slate-950 rounded-lg border border-slate-850">
                    <div class="text-xs font-bold text-white">{{ $doc->title }}</div>
                    <div class="text-[10px] text-teal-400 font-mono">{{ $doc->document_number }}</div>
                    <div class="mt-2 text-xs text-slate-300">Status: <span class="text-emerald-400 font-medium capitalize">{{ $doc->status }}</span></div>
                </div>
                @endforeach
            </div>
            @endif
        </div>
    </div>
</div>
@endsection
