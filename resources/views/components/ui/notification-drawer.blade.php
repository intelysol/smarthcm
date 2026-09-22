@props([
    'unreadCount' => 3,
])

<div x-data="{
    open: false,
    tab: 'all', // all, tasks, approvals, hr, security, system
    notifications: [
        { id: 1, type: 'approvals', title: 'Leave Request Pending Review', desc: 'Sarah Jenkins submitted 3 days annual leave request.', time: '10m ago', unread: true, url: '/portal/requests' },
        { id: 2, type: 'tasks', title: 'Quarterly Review Goal Due', desc: 'Complete self-appraisal goals before end of month.', time: '2h ago', unread: true, url: '/portal/profile' },
        { id: 3, type: 'security', title: 'New Device Login Detected', desc: 'Session established from Windows (Karachi, PK).', time: '1d ago', unread: false, url: '/portal/profile' },
        { id: 4, type: 'system', title: 'Scheduled Platform Maintenance', desc: 'System upgrade scheduled for Saturday 02:00 UTC.', time: '2d ago', unread: false, url: '#' },
    ],
    get unread() {
        return this.notifications.filter(n => n.unread).length;
    },
    get filtered() {
        if (this.tab === 'all') return this.notifications;
        return this.notifications.filter(n => n.type === this.tab);
    },
    markAllRead() {
        this.notifications.forEach(n => n.unread = false);
    }
}"
     x-on:open-notifications.window="open = true"
     x-on:keydown.escape.window="if (open) open = false"
     class="relative z-50">

    <!-- Slide-over Backdrop -->
    <div x-show="open" 
         x-transition:enter="ease-out duration-200"
         x-transition:enter-start="opacity-0"
         x-transition:enter-end="opacity-100"
         x-transition:leave="ease-in duration-150"
         x-transition:leave-start="opacity-100"
         x-transition:leave-end="opacity-0"
         class="fixed inset-0 bg-slate-900/40 backdrop-blur-xs"
         aria-hidden="true"
         style="display: none;"></div>

    <!-- Slide-over Drawer -->
    <div x-show="open"
         @click.away="open = false"
         x-transition:enter="transform transition ease-in-out duration-200"
         x-transition:enter-start="translate-x-full"
         x-transition:enter-end="translate-x-0"
         x-transition:leave="transform transition ease-in-out duration-150"
         x-transition:leave-start="translate-x-0"
         x-transition:leave-end="translate-x-full"
         class="fixed inset-y-0 right-0 max-w-md w-full bg-white shadow-2xl border-l border-slate-200 z-50 flex flex-col"
         role="region"
         aria-label="Notification Center"
         style="display: none;">

        <!-- Header -->
        <div class="p-4 border-b border-slate-200 flex items-center justify-between bg-slate-50/75">
            <div class="flex items-center space-x-2">
                <i class="fa-regular fa-bell text-slate-700"></i>
                <h2 class="text-sm font-bold text-slate-900">Notifications</h2>
                <template x-if="unread > 0">
                    <span class="px-2 py-0.5 rounded-full text-[10px] font-extrabold bg-[#C9A227] text-[#142A44]" x-text="unread + ' new'"></span>
                </template>
            </div>
            
            <div class="flex items-center space-x-2">
                <button type="button" @click="markAllRead()" class="text-[11px] text-slate-500 hover:text-slate-800 font-medium transition">
                    Mark all read
                </button>
                <button type="button" @click="open = false" aria-label="Close notifications" class="text-slate-400 hover:text-slate-600 p-1">
                    <i class="fa-solid fa-xmark text-sm"></i>
                </button>
            </div>
        </div>

        <!-- Filter Categories Tabs -->
        <div class="flex space-x-1 p-2 bg-slate-100/60 border-b border-slate-200 overflow-x-auto text-[11px]">
            <button type="button" @click="tab = 'all'" :class="tab === 'all' ? 'bg-white font-bold text-slate-900 shadow-2xs' : 'text-slate-500 hover:text-slate-700'" class="px-2.5 py-1 rounded-md transition">All</button>
            <button type="button" @click="tab = 'tasks'" :class="tab === 'tasks' ? 'bg-white font-bold text-slate-900 shadow-2xs' : 'text-slate-500 hover:text-slate-700'" class="px-2.5 py-1 rounded-md transition">Tasks</button>
            <button type="button" @click="tab = 'approvals'" :class="tab === 'approvals' ? 'bg-white font-bold text-slate-900 shadow-2xs' : 'text-slate-500 hover:text-slate-700'" class="px-2.5 py-1 rounded-md transition">Approvals</button>
            <button type="button" @click="tab = 'security'" :class="tab === 'security' ? 'bg-white font-bold text-slate-900 shadow-2xs' : 'text-slate-500 hover:text-slate-700'" class="px-2.5 py-1 rounded-md transition">Security</button>
            <button type="button" @click="tab = 'system'" :class="tab === 'system' ? 'bg-white font-bold text-slate-900 shadow-2xs' : 'text-slate-500 hover:text-slate-700'" class="px-2.5 py-1 rounded-md transition">System</button>
        </div>

        <!-- Notification List -->
        <div class="flex-1 overflow-y-auto divide-y divide-slate-100">
            <template x-if="filtered.length === 0">
                <div class="p-8 text-center text-xs text-slate-400">
                    <i class="fa-regular fa-bell-slash text-2xl mb-2 block"></i>
                    No notifications in this category.
                </div>
            </template>

            <template x-for="item in filtered" :key="item.id">
                <div :class="item.unread ? 'bg-blue-50/20' : ''" class="p-4 hover:bg-slate-50 transition flex items-start space-x-3 group">
                    <div class="w-2 h-2 rounded-full mt-1.5 shrink-0" :class="item.unread ? 'bg-[#C9A227]' : 'bg-transparent'"></div>
                    <div class="flex-1">
                        <a :href="item.url" class="block">
                            <h4 class="text-xs font-semibold text-slate-900 group-hover:text-[#1E3A5F]" x-text="item.title"></h4>
                            <p class="text-[11px] text-slate-500 mt-0.5" x-text="item.desc"></p>
                            <span class="text-[10px] text-slate-400 mt-1 block" x-text="item.time"></span>
                        </a>
                    </div>
                </div>
            </template>
        </div>

        <!-- Footer -->
        <div class="p-3 bg-slate-50 border-t border-slate-200 text-center">
            <a href="/portal/requests" class="text-xs text-[#1E3A5F] hover:underline font-semibold">
                Manage Notification Preferences
            </a>
        </div>
    </div>
</div>
