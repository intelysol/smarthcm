@extends('workforce-admin.layout')

@section('title', 'Bulk Operations')

@section('content')
<div class="space-y-6">
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-bold text-white tracking-tight">Bulk HR Operations</h1>
            <p class="text-sm text-slate-400">Perform controlled mass organizational, location, job, and status updates with dry-run pre-validation.</p>
        </div>
    </div>

    <div class="bg-slate-900 border border-slate-800 rounded-xl overflow-hidden shadow-sm">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs text-slate-300">
                <thead class="text-slate-400 bg-slate-850/60 uppercase tracking-wider border-b border-slate-800">
                    <tr>
                        <th class="py-3 px-4">Operation #</th>
                        <th class="py-3 px-4">Type</th>
                        <th class="py-3 px-4">Effective Date</th>
                        <th class="py-3 px-4">Records</th>
                        <th class="py-3 px-4">Validation</th>
                        <th class="py-3 px-4">Status</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-800">
                    @forelse($operations as $op)
                    <tr class="hover:bg-slate-850/50">
                        <td class="py-3 px-4 font-mono text-amber-300">{{ $op->operation_number }}</td>
                        <td class="py-3 px-4 font-semibold text-white">{{ ucwords(str_replace('_', ' ', $op->operation_type)) }}</td>
                        <td class="py-3 px-4">{{ $op->effective_date?->format('Y-m-d') }}</td>
                        <td class="py-3 px-4 font-mono">{{ $op->total_records }}</td>
                        <td class="py-3 px-4 font-mono">
                            @if($op->validation)
                                <span class="text-emerald-400">{{ $op->validation->valid_count }} Valid</span>,
                                <span class="text-rose-400">{{ $op->validation->error_count }} Errors</span>
                            @else
                                <span class="text-slate-500">Not validated</span>
                            @endif
                        </td>
                        <td class="py-3 px-4 font-medium">{{ ucfirst($op->status->value) }}</td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="py-8 text-center text-slate-500">No bulk operations found.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
