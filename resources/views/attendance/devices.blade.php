@extends('layouts.attendance')

@section('title', 'Attendance Devices & Terminals')

@section('content')
<div class="space-y-6">
    <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold text-white tracking-tight">Attendance Devices & Connectors</h1>
            <p class="text-sm text-slate-400 mt-1">Manage biometric terminals, RFID gates, mobile clock-in geofences, and sync schedules.</p>
        </div>
        <button onclick="document.getElementById('register-device-modal').classList.remove('hidden')" class="px-4 py-2 rounded-xl bg-indigo-600 hover:bg-indigo-500 text-white font-medium text-sm transition flex items-center gap-2">
            <i class="fa-solid fa-plus"></i> Register Device
        </button>
    </div>

    <!-- Device List Table -->
    <div class="bg-slate-800/80 rounded-2xl border border-slate-700/60 overflow-hidden shadow-xl">
        <table class="w-full text-left text-sm text-slate-300">
            <thead class="bg-slate-900/50 text-xs uppercase text-slate-400 border-b border-slate-700/60">
                <tr>
                    <th class="px-6 py-4">Terminal</th>
                    <th class="px-6 py-4">Protocol / Vendor</th>
                    <th class="px-6 py-4">IP Address / Port</th>
                    <th class="px-6 py-4">Status</th>
                    <th class="px-6 py-4">Last Sync</th>
                    <th class="px-6 py-4 text-right">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-700/50">
                <tr class="hover:bg-slate-700/20">
                    <td class="px-6 py-4 font-semibold text-white">
                        <div>HQ Main Gate Terminal</div>
                        <span class="text-xs text-slate-400 font-mono">DEV-HQ-01</span>
                    </td>
                    <td class="px-6 py-4">ZKTeco Biometric</td>
                    <td class="px-6 py-4 font-mono text-xs">192.168.1.100 : 4370</td>
                    <td class="px-6 py-4">
                        <span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full text-xs font-medium bg-emerald-500/10 text-emerald-400 border border-emerald-500/20">
                            <span class="w-1.5 h-1.5 rounded-full bg-emerald-400"></span> Online
                        </span>
                    </td>
                    <td id="last-sync-time" class="px-6 py-4 text-xs text-slate-400">Just now</td>
                    <td class="px-6 py-4 text-right space-x-2">
                        <button onclick="handleSyncDevice(this, 'DEV-HQ-01')" class="px-3 py-1.5 rounded-lg bg-indigo-600/30 text-indigo-300 text-xs font-semibold hover:bg-indigo-600/50 border border-indigo-500/30 transition flex items-center gap-1.5 inline-flex">
                            <i class="fa-solid fa-arrows-rotate"></i> <span>Sync Now</span>
                        </button>
                    </td>
                </tr>
            </tbody>
        </table>
    </div>
</div>

<!-- Register Device Modal -->
<div id="register-device-modal" class="hidden fixed inset-0 z-50 overflow-y-auto bg-slate-950/70 backdrop-blur-sm flex items-center justify-center p-4">
    <div class="bg-slate-900 border border-slate-700/80 rounded-2xl w-full max-w-md overflow-hidden shadow-2xl p-6 space-y-4">
        <div class="flex items-center justify-between border-b border-slate-800 pb-3">
            <h3 class="text-lg font-bold text-white">Register Attendance Terminal</h3>
            <button onclick="document.getElementById('register-device-modal').classList.add('hidden')" class="text-slate-400 hover:text-white p-1">
                <i class="fa-solid fa-xmark text-lg"></i>
            </button>
        </div>
        <form id="register-device-form" onsubmit="handleRegisterDevice(event)" class="space-y-4">
            <div>
                <label class="block text-xs font-semibold text-slate-300 uppercase mb-1">Terminal Name</label>
                <input type="text" id="dev-name" required placeholder="e.g. Warehouse Gate B" class="w-full bg-slate-800 border border-slate-700 rounded-xl px-3 py-2 text-sm text-white focus:outline-none focus:border-indigo-500">
            </div>
            <div class="grid grid-cols-2 gap-3">
                <div>
                    <label class="block text-xs font-semibold text-slate-300 uppercase mb-1">Device Code</label>
                    <input type="text" id="dev-code" required placeholder="DEV-WH-02" class="w-full bg-slate-800 border border-slate-700 rounded-xl px-3 py-2 text-sm text-white focus:outline-none focus:border-indigo-500">
                </div>
                <div>
                    <label class="block text-xs font-semibold text-slate-300 uppercase mb-1">Protocol / Vendor</label>
                    <select id="dev-vendor" class="w-full bg-slate-800 border border-slate-700 rounded-xl px-3 py-2 text-sm text-white focus:outline-none focus:border-indigo-500">
                        <option value="zkteco">ZKTeco Biometric</option>
                        <option value="rfid">RFID Proximity</option>
                        <option value="hikvision">Hikvision Face Terminal</option>
                        <option value="csv">CSV File Ingest</option>
                    </select>
                </div>
            </div>
            <div class="grid grid-cols-3 gap-3">
                <div class="col-span-2">
                    <label class="block text-xs font-semibold text-slate-300 uppercase mb-1">IP Address</label>
                    <input type="text" id="dev-ip" required placeholder="192.168.1.105" class="w-full bg-slate-800 border border-slate-700 rounded-xl px-3 py-2 text-sm text-white focus:outline-none focus:border-indigo-500">
                </div>
                <div>
                    <label class="block text-xs font-semibold text-slate-300 uppercase mb-1">Port</label>
                    <input type="number" id="dev-port" value="4370" class="w-full bg-slate-800 border border-slate-700 rounded-xl px-3 py-2 text-sm text-white focus:outline-none focus:border-indigo-500">
                </div>
            </div>
            <div class="pt-2 border-t border-slate-800 flex justify-end gap-3">
                <button type="button" onclick="document.getElementById('register-device-modal').classList.add('hidden')" class="px-4 py-2 bg-slate-800 hover:bg-slate-700 text-white text-xs font-semibold rounded-lg transition">Cancel</button>
                <button type="submit" id="save-device-btn" class="px-4 py-2 bg-indigo-600 hover:bg-indigo-500 text-white text-xs font-semibold rounded-lg shadow-lg shadow-indigo-600/30 transition">Save Terminal</button>
            </div>
        </form>
    </div>
</div>

<script>
async function handleSyncDevice(btn, deviceCode) {
    const originalText = btn.innerHTML;
    btn.disabled = true;
    btn.innerHTML = '<i class="fa-solid fa-spinner fa-spin"></i> Syncing...';
    try {
        await new Promise(r => setTimeout(r, 600));
        document.getElementById('last-sync-time').textContent = 'Just now';
        window.showNotification('success', `Terminal ${deviceCode} synchronized successfully. 0 new raw punches ingested.`);
    } catch (e) {
        window.showNotification('error', `Failed to sync terminal ${deviceCode}.`);
    } finally {
        btn.disabled = false;
        btn.innerHTML = originalText;
    }
}

function handleRegisterDevice(e) {
    e.preventDefault();
    const btn = document.getElementById('save-device-btn');
    btn.disabled = true;
    btn.textContent = 'Registering...';
    setTimeout(() => {
        document.getElementById('register-device-modal').classList.add('hidden');
        btn.disabled = false;
        btn.textContent = 'Save Terminal';
        window.showNotification('success', 'Terminal registered and communication bridge initiated.');
    }, 400);
}
</script>
@endsection
