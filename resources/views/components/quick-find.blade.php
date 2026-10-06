@auth
@php
    $user = auth()->user();
    $isAdmin = $user->isAdmin || $user->isSuperadmin;
    $payrollLocked   = \App\Helpers\Editions::payrollLocked();
    $reportingLocked = \App\Helpers\Editions::reportingLocked();
    $auditLocked     = \App\Helpers\Editions::auditLocked();

    $safe = fn(string $name) => \Illuminate\Support\Facades\Route::has($name) ? route($name) : '#';

    // Build a flat command list, role-aware. {label, hint, section, url, locked}
    $commands = [];

    if ($isAdmin) {
        $commands = [
            ['section' => 'Workspace', 'label' => 'Dashboard',          'hint' => 'Today\'s overview',     'url' => $safe('admin.dashboard')],
            ['section' => 'Workspace', 'label' => 'Approvals',          'hint' => 'Leave / overtime / claims', 'url' => $safe('admin.leaves')],
            ['section' => 'Workspace', 'label' => 'Staff',              'hint' => 'All employees',         'url' => $safe('admin.employees')],
            ['section' => 'Workspace', 'label' => 'Notifications',      'hint' => 'In-app inbox',          'url' => $safe('notifications')],
            ['section' => 'Workspace', 'label' => 'Profile',            'hint' => 'Your account',          'url' => $safe('profile.show')],

            ['section' => 'Attendance','label' => 'Daily log',          'hint' => 'All check-ins today',   'url' => $safe('admin.attendances')],
            ['section' => 'Attendance','label' => 'Overtime',           'hint' => 'Extra hours',           'url' => $safe('admin.overtime')],
            ['section' => 'Attendance','label' => 'Schedules',          'hint' => 'Shift rotations',       'url' => $safe('admin.schedules')],
            ['section' => 'Attendance','label' => 'Holidays',           'hint' => 'Calendar',              'url' => $safe('admin.holidays')],
            ['section' => 'Attendance','label' => 'Announcements',      'hint' => 'Org-wide notices',      'url' => $safe('admin.announcements')],
            ['section' => 'Attendance','label' => 'Analytics',          'hint' => 'Trends & insights',     'url' => $safe('admin.analytics'), 'locked' => $reportingLocked],

            ['section' => 'Finance',   'label' => 'Payroll',            'hint' => 'Run & history',         'url' => $safe('admin.payrolls'),       'locked' => $payrollLocked],
            ['section' => 'Finance',   'label' => 'Payroll settings',   'hint' => 'Components',            'url' => $safe('admin.payroll.settings'), 'locked' => $payrollLocked],
            ['section' => 'Finance',   'label' => 'Reimbursements',     'hint' => 'Claims & receipts',     'url' => $safe('admin.reimbursements'), 'locked' => $payrollLocked],
            ['section' => 'Finance',   'label' => 'Cash advances',      'hint' => 'Outstanding',           'url' => $safe('admin.manage-kasbon'),  'locked' => $payrollLocked],

            ['section' => 'Setup',     'label' => 'Master data',        'hint' => 'Divisions / Job titles', 'url' => $safe('admin.masters.division')],
            ['section' => 'Setup',     'label' => 'Barcode locations',  'hint' => 'Check-in points',       'url' => $safe('admin.barcodes')],
            ['section' => 'Setup',     'label' => 'Geofence locations', 'hint' => 'GPS check-in areas',     'url' => $safe('admin.geofence-locations')],
            ['section' => 'Setup',     'label' => 'Import employees',   'hint' => 'Bulk data',             'url' => $safe('admin.import-export.users'), 'locked' => $reportingLocked],
            ['section' => 'Setup',     'label' => 'Import attendance',  'hint' => 'Bulk data',             'url' => $safe('admin.import-export.attendances'), 'locked' => $reportingLocked],
            ['section' => 'Setup',     'label' => 'Activity logs',      'hint' => '30-day audit trail',    'url' => $safe('admin.activity-logs'), 'locked' => $auditLocked],
        ];
        if ($user->isSuperadmin) {
            $commands[] = ['section' => 'Setup', 'label' => 'Settings', 'hint' => 'Org · billing', 'url' => $safe('admin.settings')];
            $commands[] = ['section' => 'Setup', 'label' => 'Maintenance', 'hint' => 'System tools', 'url' => $safe('admin.system-maintenance')];
        }
    } else {
        $commands = [
            ['section' => 'Workspace', 'label' => 'Dashboard',     'hint' => 'Home',                 'url' => $safe('home')],
            ['section' => 'Workspace', 'label' => 'Time Sheet',    'hint' => 'My schedule',          'url' => $safe('my-schedule')],
            ['section' => 'Workspace', 'label' => 'Requests',      'hint' => 'Apply for leave',      'url' => $safe('apply-leave')],
            ['section' => 'Workspace', 'label' => 'Notifications', 'hint' => 'In-app inbox',         'url' => $safe('notifications')],
            ['section' => 'Workspace', 'label' => 'Profile',       'hint' => 'Your account',         'url' => $safe('profile.show')],
            ['section' => 'Personal',  'label' => 'Payslips',      'hint' => 'View & download',      'url' => $safe('my-payslips')],
            ['section' => 'Personal',  'label' => 'Attendance history', 'hint' => 'Past clock-ins', 'url' => $safe('attendance-history')],
            ['section' => 'Personal',  'label' => 'Overtime',      'hint' => 'Extra hours',          'url' => $safe('overtime')],
            ['section' => 'Personal',  'label' => 'Reimbursement', 'hint' => 'Claims',               'url' => $safe('reimbursement')],
            ['section' => 'Personal',  'label' => 'Cash advances', 'hint' => 'My advances',          'url' => $safe('my-kasbon')],
        ];
        if ($user->subordinates->isNotEmpty()) {
            $commands[] = ['section' => 'Team', 'label' => 'Team approvals', 'hint' => 'Subordinates', 'url' => $safe('approvals')];
            $commands[] = ['section' => 'Team', 'label' => 'Approval history', 'hint' => 'Past decisions', 'url' => $safe('approvals.history')];
        }
    }

    // Common to both
    $commands[] = ['section' => 'Account', 'label' => 'Toggle theme', 'hint' => 'Light / dark', 'action' => 'theme'];
    $commands[] = ['section' => 'Account', 'label' => 'Sign out',     'hint' => 'End session',  'action' => 'logout'];
@endphp

<div
    x-data="quickFind({{ \Illuminate\Support\Js::from($commands) }})"
    x-on:keydown.window.prevent.meta.k="open()"
    x-on:keydown.window.prevent.ctrl.k="open()"
    x-on:open-quick-find.window="open()"
>
    <template x-if="isOpen">
        <div class="ds-cmd-backdrop" @click.self="close()" @keydown.escape.window="close()">
            <div class="ds-cmd-panel" @click.stop x-show="isOpen"
                 x-transition:enter="transition ease-out duration-150"
                 x-transition:enter-start="opacity-0 scale-95"
                 x-transition:enter-end="opacity-100 scale-100">

                <div class="ds-cmd-input-wrap">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.7" stroke="currentColor" class="h-5 w-5">
                        <path stroke-linecap="round" stroke-linejoin="round" d="m21 21-5.197-5.197m0 0A7.5 7.5 0 1 0 5.196 5.196a7.5 7.5 0 0 0 10.607 10.607Z" />
                    </svg>
                    <input
                        x-ref="cmdInput"
                        type="text"
                        x-model="query"
                        @input="selected = 0"
                        @keydown.arrow-down.prevent="moveSel(1)"
                        @keydown.arrow-up.prevent="moveSel(-1)"
                        @keydown.enter.prevent="activate(filtered[selected])"
                        placeholder="{{ __('Search pages, actions...') }}"
                        class="ds-cmd-input"
                        autocomplete="off"
                        spellcheck="false"
                    >
                    <kbd>esc</kbd>
                </div>

                <div class="ds-cmd-list">
                    <template x-if="filtered.length === 0">
                        <div class="ds-cmd-empty">{{ __('No matches') }}</div>
                    </template>

                    <template x-for="(group, gi) in grouped" :key="gi">
                        <div>
                            <div class="ds-cmd-section-title" x-text="group.title"></div>
                            <template x-for="cmd in group.items" :key="cmd._idx">
                                <div
                                    class="ds-cmd-item"
                                    :class="filtered.indexOf(cmd) === selected ? 'is-selected' : ''"
                                    @mouseenter="selected = filtered.indexOf(cmd)"
                                    @click="activate(cmd)"
                                >
                                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.7" stroke="currentColor" class="icn">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="m12.75 15 3-3m0 0-3-3m3 3h-7.5M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z"/>
                                    </svg>
                                    <span class="cmd-label" x-text="cmd.label"></span>
                                    <span x-show="cmd.locked" class="cmd-hint">{{ __('Locked') }}</span>
                                    <span class="cmd-hint" x-text="cmd.hint" x-show="!cmd.locked"></span>
                                </div>
                            </template>
                        </div>
                    </template>
                </div>

                <div class="ds-cmd-foot">
                    <span><kbd>↑</kbd><kbd>↓</kbd> navigate</span>
                    <span><kbd>↵</kbd> select</span>
                    <span><kbd>esc</kbd> close</span>
                </div>
            </div>
        </div>
    </template>
</div>

<form id="quickfind-logout-form" method="POST" action="{{ route('logout') }}" style="display: none;">@csrf</form>

@once
@push('scripts')
<script>
document.addEventListener('alpine:init', () => {
    Alpine.data('quickFind', (commands) => ({
        isOpen: false,
        query: '',
        selected: 0,
        commands: commands.map((c, i) => ({ ...c, _idx: i })),
        get filtered() {
            const q = this.query.trim().toLowerCase();
            if (!q) return this.commands;
            return this.commands.filter(c =>
                c.label.toLowerCase().includes(q) ||
                (c.hint || '').toLowerCase().includes(q) ||
                (c.section || '').toLowerCase().includes(q)
            );
        },
        get grouped() {
            const g = {};
            for (const cmd of this.filtered) {
                const k = cmd.section || 'Other';
                (g[k] ||= []).push(cmd);
            }
            return Object.entries(g).map(([title, items]) => ({ title, items }));
        },
        open() {
            this.isOpen = true;
            this.query = '';
            this.selected = 0;
            this.$nextTick(() => this.$refs.cmdInput?.focus());
        },
        close() { this.isOpen = false; },
        moveSel(dir) {
            const n = this.filtered.length;
            if (!n) return;
            this.selected = (this.selected + dir + n) % n;
            // ensure visible
            this.$nextTick(() => {
                const sel = document.querySelector('.ds-cmd-item.is-selected');
                sel?.scrollIntoView({ block: 'nearest' });
            });
        },
        activate(cmd) {
            if (!cmd) return;
            if (cmd.locked) {
                window.dispatchEvent(new CustomEvent('feature-lock', {
                    detail: { title: cmd.label + ' Locked', message: 'This is an Enterprise Feature. Please upgrade.' }
                }));
                this.close();
                return;
            }
            if (cmd.action === 'theme') {
                Alpine.store('darkMode')?.toggle();
                this.close();
                return;
            }
            if (cmd.action === 'logout') {
                document.getElementById('quickfind-logout-form')?.submit();
                return;
            }
            if (cmd.url && cmd.url !== '#') {
                this.close();
                // Use Livewire's SPA navigation when available, fallback to hard nav
                if (window.Livewire?.navigate) Livewire.navigate(cmd.url);
                else window.location.href = cmd.url;
            }
        }
    }));
});
</script>
@endpush
@endonce
@endauth
