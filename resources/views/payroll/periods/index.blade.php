@extends('payroll.layout')

@section('title', 'Payroll Periods')
@section('page_title', 'Payroll Calendars & Periods')

@section('content')
<div class="space-y-6">
    <div class="flex items-center justify-between">
        <div>
            <h3 class="text-lg font-semibold text-white">Configured Periods</h3>
            <p class="text-sm text-slate-400">Manage payroll period schedules, cutoff dates, and audit locking.</p>
        </div>
    </div>

    <div class="bg-slate-950 border border-slate-800 rounded-xl overflow-hidden shadow-sm">
        <table class="w-full text-left text-sm">
            <thead class="bg-slate-900/80 border-b border-slate-800 text-slate-400 text-xs uppercase font-medium">
                <tr>
                    <th class="px-6 py-4">Period Name</th>
                    <th class="px-6 py-4">Start Date</th>
                    <th class="px-6 py-4">End Date</th>
                    <th class="px-6 py-4">Cutoff Date</th>
                    <th class="px-6 py-4">Payment Date</th>
                    <th class="px-6 py-4">Status</th>
                    <th class="px-6 py-4 text-right">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-800 text-slate-300">
                @forelse($periods as $period)
                <tr class="hover:bg-slate-900/50 transition">
                    <td class="px-6 py-4 font-medium text-white">{{ $period->period_name }}</td>
                    <td class="px-6 py-4 font-mono text-xs">{{ $period->start_date->format('Y-m-d') }}</td>
                    <td class="px-6 py-4 font-mono text-xs">{{ $period->end_date->format('Y-m-d') }}</td>
                    <td class="px-6 py-4 font-mono text-xs">{{ $period->cutoff_date ? $period->cutoff_date->format('Y-m-d') : '-' }}</td>
                    <td class="px-6 py-4 font-mono text-xs">{{ $period->payment_date ? $period->payment_date->format('Y-m-d') : '-' }}</td>
                    <td class="px-6 py-4">
                        <span class="inline-flex px-2.5 py-1 rounded text-xs font-semibold {{ $period->isLocked() ? 'bg-rose-500/10 text-rose-400 border border-rose-500/20' : 'bg-emerald-500/10 text-emerald-400 border border-emerald-500/20' }}">
                            {{ strtoupper($period->status) }}
                        </span>
                    </td>
                    <td class="px-6 py-4 text-right space-x-2">
                        @if(! $period->isLocked())
                        <button onclick="handlePeriodLock(this, '{{ $period->id }}')" class="text-xs px-3 py-1.5 rounded bg-rose-600/20 text-rose-400 hover:bg-rose-600/30 border border-rose-500/30 transition">
                            <i class="fa-solid fa-lock mr-1"></i> Lock
                        </button>
                        @else
                        <button onclick="handlePeriodLock(this, '{{ $period->id }}')" class="text-xs px-3 py-1.5 rounded bg-amber-600/20 text-amber-400 hover:bg-amber-600/30 border border-amber-500/30 transition">
                            <i class="fa-solid fa-lock-open mr-1"></i> Reopen
                        </button>
                        @endif
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="7" class="px-6 py-8 text-center text-slate-500">No payroll periods found.</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div>
        {{ $periods->links() }}
    </div>
</div>

<script>
    async function handlePeriodLock(btn, periodId) {
        btn.disabled = true;
        btn.innerText = '...';

        try {
            const res = await fetch(`/api/v1/hcm/payroll/periods/${periodId}/lock`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                }
            });
            setTimeout(() => location.reload(), 600);
        } catch (err) {
            btn.disabled = false;
        }
    }
</script>
@endsection
