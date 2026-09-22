@extends('shells.tenant')

@section('title', 'Users & Access Control — Tenant Admin')

@section('content')
<div class="space-y-6">
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h1 class="text-xl font-bold text-slate-900">Users &amp; Access Control</h1>
            <p class="text-xs text-slate-500">Manage user authentication credentials, activation states, and tenant role assignments</p>
        </div>
        <div class="flex items-center space-x-3">
            <button type="button" onclick="document.getElementById('createUserModal').classList.remove('hidden')" class="px-3.5 py-2 rounded-lg bg-[#1E3A5F] hover:bg-[#142A44] text-white text-xs font-semibold shadow-sm transition flex items-center">
                <i class="fa-solid fa-user-plus mr-1.5 text-[#C9A227]"></i> Add New User
            </button>
            <a href="{{ route('admin.dashboard') }}" class="text-xs font-semibold text-[#1E3A5F] hover:underline">&larr; Back to Overview</a>
        </div>
    </div>

    @if(session('status'))
        <div class="p-3.5 rounded-xl bg-emerald-50 border border-emerald-200 text-xs text-emerald-800 flex items-center justify-between">
            <div class="flex items-center">
                <i class="fa-solid fa-circle-check text-emerald-600 mr-2 text-sm"></i>
                <span>{{ session('status') }}</span>
            </div>
            <button onclick="this.parentElement.remove()" class="text-emerald-700 hover:text-emerald-900">&times;</button>
        </div>
    @endif

    @if(session('warning'))
        <div class="p-3.5 rounded-xl bg-amber-50 border border-amber-200 text-xs text-amber-800 flex items-center justify-between">
            <div class="flex items-center">
                <i class="fa-solid fa-triangle-exclamation text-amber-600 mr-2 text-sm"></i>
                <span>{{ session('warning') }}</span>
            </div>
            <button onclick="this.parentElement.remove()" class="text-amber-700 hover:text-amber-900">&times;</button>
        </div>
    @endif

    @if($errors->any())
        <div class="p-3.5 rounded-xl bg-rose-50 border border-rose-200 text-xs text-rose-800">
            <ul class="list-disc list-inside space-y-1">
                @foreach($errors->all() as $err)
                    <li>{{ $err }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <!-- Users Table Card -->
    <div class="bg-white border border-slate-200 rounded-xl shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs text-slate-600">
                <thead class="bg-slate-50 text-slate-500 uppercase text-[10px] tracking-wider border-b border-slate-200 font-semibold">
                    <tr>
                        <th class="px-5 py-3">User &amp; Email</th>
                        <th class="px-5 py-3">Assigned Roles</th>
                        <th class="px-5 py-3">Account Status</th>
                        <th class="px-5 py-3">Last Sign In</th>
                        <th class="px-5 py-3 text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($users as $u)
                        <tr class="hover:bg-slate-50/80 transition">
                            <td class="px-5 py-3.5">
                                <div class="font-bold text-slate-900">{{ $u->name }}</div>
                                <div class="text-[11px] font-mono text-slate-500">{{ $u->email }}</div>
                            </td>
                            <td class="px-5 py-3.5">
                                <div class="flex flex-wrap gap-1">
                                    @forelse($u->roles as $r)
                                        <span class="px-2 py-0.5 rounded text-[10px] font-bold bg-indigo-50 text-indigo-700 border border-indigo-100">
                                            {{ $r->label ?? $r->name }}
                                        </span>
                                    @empty
                                        <span class="text-[11px] text-slate-400 italic">No assigned role</span>
                                    @endforelse
                                </div>
                            </td>
                            <td class="px-5 py-3.5">
                                <span class="px-2 py-0.5 rounded text-[10px] font-bold {{ $u->status === 'active' ? 'bg-emerald-100 text-emerald-800' : 'bg-rose-100 text-rose-800' }}">
                                    {{ strtoupper($u->status ?? 'ACTIVE') }}
                                </span>
                            </td>
                            <td class="px-5 py-3.5 text-[11px] text-slate-500">
                                {{ $u->last_login_at ? \Carbon\Carbon::parse($u->last_login_at)->diffForHumans() : 'Never logged in' }}
                            </td>
                            <td class="px-5 py-3.5 text-right space-x-2">
                                <!-- Status Toggle -->
                                <form method="POST" action="{{ route('admin.users.status', $u->id) }}" class="inline">
                                    @csrf
                                    <button type="submit" class="px-2.5 py-1 rounded text-[11px] font-semibold border {{ $u->status === 'active' ? 'border-rose-200 text-rose-700 hover:bg-rose-50' : 'border-emerald-200 text-emerald-700 hover:bg-emerald-50' }} transition">
                                        {{ $u->status === 'active' ? 'Deactivate' : 'Activate' }}
                                    </button>
                                </form>

                                <!-- Password Reset -->
                                <form method="POST" action="{{ route('admin.users.reset-password', $u->id) }}" class="inline" onsubmit="return confirm('Reset password for {{ $u->email }}?')">
                                    @csrf
                                    <button type="submit" class="px-2.5 py-1 rounded text-[11px] font-semibold border border-slate-200 text-slate-600 hover:bg-slate-50 transition" title="Reset password to default demo credentials">
                                        Reset PW
                                    </button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="px-5 py-8 text-center text-slate-400">No users found for this organization.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($users->hasPages())
        <div class="px-5 py-3 border-t border-slate-100 bg-slate-50 text-xs">
            {{ $users->links() }}
        </div>
        @endif
    </div>

    <!-- Create User Modal -->
    <div id="createUserModal" class="fixed inset-0 bg-slate-900/60 backdrop-blur-sm z-50 flex items-center justify-center p-4 hidden">
        <div class="bg-white rounded-2xl max-w-md w-full shadow-2xl border border-slate-200 overflow-hidden">
            <div class="px-6 py-4 bg-slate-50 border-b border-slate-200 flex items-center justify-between">
                <h3 class="font-bold text-sm text-slate-900 flex items-center gap-2">
                    <i class="fa-solid fa-user-plus text-[#1E3A5F]"></i> Add New Organization User
                </h3>
                <button type="button" onclick="document.getElementById('createUserModal').classList.add('hidden')" class="text-slate-400 hover:text-slate-600 font-bold">&times;</button>
            </div>
            <form method="POST" action="{{ route('admin.users.create') }}" class="p-6 space-y-4 text-xs">
                @csrf
                <div>
                    <label class="block font-semibold text-slate-700 mb-1">Full Name</label>
                    <input type="text" name="name" required placeholder="e.g. Jordan Hayes" class="w-full px-3 py-2 rounded-lg border border-slate-300 focus:outline-none focus:ring-2 focus:ring-[#1E3A5F]">
                </div>
                <div>
                    <label class="block font-semibold text-slate-700 mb-1">Corporate Email Address</label>
                    <input type="email" name="email" required placeholder="jordan@example.test" class="w-full px-3 py-2 rounded-lg border border-slate-300 focus:outline-none focus:ring-2 focus:ring-[#1E3A5F]">
                </div>
                <div>
                    <label class="block font-semibold text-slate-700 mb-1">Assign Role</label>
                    <select name="role_id" class="w-full px-3 py-2 rounded-lg border border-slate-300 focus:outline-none focus:ring-2 focus:ring-[#1E3A5F]">
                        <option value="">-- Select Role --</option>
                        @foreach($roles as $r)
                            <option value="{{ $r->id }}">{{ $r->label ?? $r->name }} ({{ $r->type }})</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block font-semibold text-slate-700 mb-1">Initial Password (optional)</label>
                    <input type="password" name="password" placeholder="Leave empty for DEMO_USER_PASSWORD default" class="w-full px-3 py-2 rounded-lg border border-slate-300 focus:outline-none focus:ring-2 focus:ring-[#1E3A5F]">
                </div>
                <div class="pt-3 border-t border-slate-100 flex items-center justify-end space-x-2">
                    <button type="button" onclick="document.getElementById('createUserModal').classList.add('hidden')" class="px-3.5 py-2 rounded-lg border border-slate-300 text-slate-600 hover:bg-slate-50 font-medium">Cancel</button>
                    <button type="submit" class="px-4 py-2 rounded-lg bg-[#1E3A5F] hover:bg-[#142A44] text-white font-semibold shadow">Create User</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
