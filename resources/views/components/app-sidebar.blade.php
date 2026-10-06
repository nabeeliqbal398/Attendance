@auth
@php
    $user = auth()->user();
    $isAdmin = $user->isAdmin || $user->isSuperadmin;
    $route = request()->route()?->getName() ?? '';
    $initials = collect(explode(' ', trim($user->name)))->take(2)->map(fn($p) => mb_substr($p, 0, 1))->implode('');

    $isActive = function (array $names) use ($route): bool {
        foreach ($names as $n) {
            if (str_starts_with($n, '*')) {
                if (str_starts_with($route, substr($n, 1))) return true;
            } else {
                if ($route === $n) return true;
            }
        }
        return false;
    };

    // Pending counts for sidebar badges (cheap query, cached for 60s)
    $pendingLeavesCount = $isAdmin ? cache()->remember('sb-pending-leaves', 60, fn() => \App\Models\Attendance::where('approval_status', 'pending')->whereIn('status', ['sick', 'excused', 'leave', 'permission'])->count()) : 0;

    // Edition gates — Payroll / Reimbursements / Analytics / Activity Logs are Enterprise-only
    $payrollLocked    = \App\Helpers\Editions::payrollLocked();
    $reportingLocked  = \App\Helpers\Editions::reportingLocked();
    $auditLocked      = \App\Helpers\Editions::auditLocked();
    $attendanceLocked = \App\Helpers\Editions::attendanceLocked();
@endphp

<aside
    class="ds-sidebar hidden sm:flex"
    x-data="{
        collapsed: localStorage.getItem('ds-sb-collapsed') === '1',
        toggle() { this.collapsed = !this.collapsed; localStorage.setItem('ds-sb-collapsed', this.collapsed ? '1' : '0'); document.body.dataset.sbCollapsed = this.collapsed ? 'true' : 'false'; },
        init() { document.body.dataset.sbCollapsed = this.collapsed ? 'true' : 'false'; }
    }"
    :data-collapsed="collapsed"
>
    {{-- Brand --}}
    <a href="{{ $isAdmin ? route('admin.dashboard') : route('home') }}" wire:navigate.hover class="ds-sb-brand">
        <div class="ds-sb-mark">I</div>
        <span class="ds-sb-brand-text">{{ config('app.name', 'Attendance App') }}</span>
    </a>

    {{-- Collapse toggle --}}
    <button type="button" @click="toggle()" class="ds-sb-toggle" :aria-label="collapsed ? 'Expand sidebar' : 'Collapse sidebar'">
        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="h-3 w-3" :class="collapsed ? '' : 'rotate-180'">
            <path stroke-linecap="round" stroke-linejoin="round" d="m8.25 4.5 7.5 7.5-7.5 7.5" />
        </svg>
    </button>

    {{-- Search trigger — opens the Quick Find palette (also bound to ⌘K / Ctrl+K) --}}
    <button type="button"
            class="ds-sb-search"
            @click="window.dispatchEvent(new CustomEvent('open-quick-find'))"
            aria-label="{{ __('Open quick find') }}">
        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.7" stroke="currentColor" class="h-4 w-4">
            <path stroke-linecap="round" stroke-linejoin="round" d="m21 21-5.197-5.197m0 0A7.5 7.5 0 1 0 5.196 5.196a7.5 7.5 0 0 0 10.607 10.607Z" />
        </svg>
        <span class="ds-sb-search-text">{{ __('Quick find') }}</span>
        <kbd>⌘K</kbd>
    </button>

    {{-- Scroll area --}}
    <nav class="ds-sb-scroll" aria-label="{{ __('Primary navigation') }}">
        @if($isAdmin)
            {{-- ADMIN --}}
            <div class="ds-sb-group">
                <div class="ds-sb-group-title">{{ __('Workspace') }}</div>
                <a href="{{ route('admin.dashboard') }}" wire:navigate.hover class="ds-sb-link {{ $isActive(['admin.dashboard']) ? 'active' : '' }}">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.7" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M3.75 6A2.25 2.25 0 0 1 6 3.75h2.25A2.25 2.25 0 0 1 10.5 6v2.25a2.25 2.25 0 0 1-2.25 2.25H6a2.25 2.25 0 0 1-2.25-2.25V6ZM3.75 15.75A2.25 2.25 0 0 1 6 13.5h2.25a2.25 2.25 0 0 1 2.25 2.25V18a2.25 2.25 0 0 1-2.25 2.25H6A2.25 2.25 0 0 1 3.75 18v-2.25ZM13.5 6a2.25 2.25 0 0 1 2.25-2.25H18A2.25 2.25 0 0 1 20.25 6v2.25A2.25 2.25 0 0 1 18 10.5h-2.25a2.25 2.25 0 0 1-2.25-2.25V6ZM13.5 15.75a2.25 2.25 0 0 1 2.25-2.25H18a2.25 2.25 0 0 1 2.25 2.25V18A2.25 2.25 0 0 1 18 20.25h-2.25A2.25 2.25 0 0 1 13.5 18v-2.25Z" /></svg>
                    <span class="lbl-text">{{ __('Dashboard') }}</span>
                </a>
                <a href="{{ route('admin.leaves') }}" wire:navigate.hover class="ds-sb-link {{ $isActive(['*admin.leaves', '*admin.overtime']) ? 'active' : '' }}">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.7" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M2.25 13.5h3.86a2.25 2.25 0 0 1 2.012 1.244l.256.512a2.25 2.25 0 0 0 2.013 1.244h3.218a2.25 2.25 0 0 0 2.013-1.244l.256-.512a2.25 2.25 0 0 1 2.013-1.244h3.859m-19.5.338V18a2.25 2.25 0 0 0 2.25 2.25h15A2.25 2.25 0 0 0 21.75 18v-4.162c0-.224-.034-.447-.1-.661L19.24 5.338a2.25 2.25 0 0 0-2.15-1.588H6.911a2.25 2.25 0 0 0-2.15 1.588L2.35 13.177a2.25 2.25 0 0 0-.1.661Z" /></svg>
                    <span class="lbl-text">{{ __('Approvals') }}</span>
                    @if($pendingLeavesCount > 0)
                        <span class="ds-sb-pill">{{ $pendingLeavesCount }}</span>
                    @endif
                </a>
                <a href="{{ route('admin.employees') }}" wire:navigate.hover class="ds-sb-link {{ $isActive(['*admin.employees']) ? 'active' : '' }}">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.7" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M15 19.128a9.38 9.38 0 0 0 2.625.372 9.337 9.337 0 0 0 4.121-.952 4.125 4.125 0 0 0-7.533-2.493M15 19.128v-.003c0-1.113-.285-2.16-.786-3.07M15 19.128v.106A12.318 12.318 0 0 1 8.624 21c-2.331 0-4.512-.645-6.374-1.766l-.001-.109a6.375 6.375 0 0 1 11.964-3.07M12 6.375a3.375 3.375 0 1 1-6.75 0 3.375 3.375 0 0 1 6.75 0Zm8.25 2.25a2.625 2.625 0 1 1-5.25 0 2.625 2.625 0 0 1 5.25 0Z" /></svg>
                    <span class="lbl-text">{{ __('Staff') }}</span>
                </a>
                <a href="{{ route('notifications') }}" wire:navigate.hover class="ds-sb-link {{ $isActive(['notifications']) ? 'active' : '' }}">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.7" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M14.857 17.082a23.848 23.848 0 0 0 5.454-1.31A8.967 8.967 0 0 1 18 9.75V9A6 6 0 0 0 6 9v.75a8.967 8.967 0 0 1-2.312 6.022c1.733.64 3.56 1.085 5.455 1.31m5.714 0a24.255 24.255 0 0 1-5.714 0m5.714 0a3 3 0 1 1-5.714 0" /></svg>
                    <span class="lbl-text">{{ __('Notifications') }}</span>
                </a>
            </div>

            <div class="ds-sb-group">
                <div class="ds-sb-group-title">{{ __('Attendance') }}</div>
                <a href="{{ route('admin.attendances') }}" wire:navigate.hover class="ds-sb-link {{ $isActive(['*admin.attendances']) ? 'active' : '' }}">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.7" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M3 13.125C3 12.504 3.504 12 4.125 12h2.25c.621 0 1.125.504 1.125 1.125v6.75C7.5 20.496 6.996 21 6.375 21h-2.25A1.125 1.125 0 0 1 3 19.875v-6.75ZM9.75 8.625c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125v11.25c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 0 1-1.125-1.125V8.625ZM16.5 4.125c0-.621.504-1.125 1.125-1.125h2.25C20.496 3 21 3.504 21 4.125v15.75c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 0 1-1.125-1.125V4.125Z" /></svg>
                    <span class="lbl-text">{{ __('Daily log') }}</span>
                </a>
                <a href="{{ route('admin.schedules') }}" wire:navigate.hover class="ds-sb-link {{ $isActive(['*admin.schedules']) ? 'active' : '' }}">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.7" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M6.75 3v2.25M17.25 3v2.25M3 18.75V7.5a2.25 2.25 0 0 1 2.25-2.25h13.5A2.25 2.25 0 0 1 21 7.5v11.25" /></svg>
                    <span class="lbl-text">{{ __('Schedules') }}</span>
                </a>
                <a href="{{ route('admin.holidays') }}" wire:navigate.hover class="ds-sb-link {{ $isActive(['*admin.holidays']) ? 'active' : '' }}">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.7" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M11.48 3.499a.562.562 0 0 1 1.04 0l2.125 5.111a.563.563 0 0 0 .475.345l5.518.442c.499.04.701.663.321.988l-4.204 3.602a.563.563 0 0 0-.182.557l1.285 5.385a.562.562 0 0 1-.84.61l-4.725-2.885a.562.562 0 0 0-.586 0L6.982 20.54a.562.562 0 0 1-.84-.61l1.285-5.386a.562.562 0 0 0-.182-.557l-4.204-3.602a.562.562 0 0 1 .321-.988l5.518-.442a.563.563 0 0 0 .475-.345L11.48 3.5Z" /></svg>
                    <span class="lbl-text">{{ __('Holidays') }}</span>
                </a>
                <a href="{{ route('admin.announcements') }}" wire:navigate.hover class="ds-sb-link {{ $isActive(['*admin.announcements']) ? 'active' : '' }}">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.7" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M10.34 15.84c-.688-.06-1.386-.09-2.09-.09H7.5a4.5 4.5 0 1 1 0-9h.75c.704 0 1.402-.03 2.09-.09" /></svg>
                    <span class="lbl-text">{{ __('Announcements') }}</span>
                </a>
                @php
                    $analyticsLocked = \App\Helpers\Editions::reportingLocked();
                    $lockSvgInline = '<svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="lock-ic"><path stroke-linecap="round" stroke-linejoin="round" d="M16.5 10.5V6.75a4.5 4.5 0 1 0-9 0v3.75m-.75 11.25h10.5a2.25 2.25 0 0 0 2.25-2.25v-6.75a2.25 2.25 0 0 0-2.25-2.25H6.75a2.25 2.25 0 0 0-2.25 2.25v6.75a2.25 2.25 0 0 0 2.25 2.25Z"/></svg>';
                @endphp
                <a href="{{ $analyticsLocked ? '#' : route('admin.analytics') }}"
                   @if($analyticsLocked) @click.prevent="$dispatch('feature-lock', { title: 'Analytics Locked', message: 'Advanced Analytics is an Enterprise Feature. Please upgrade.' })" @else wire:navigate.hover @endif
                   class="ds-sb-link {{ $analyticsLocked ? 'locked' : '' }} {{ $isActive(['*admin.analytics']) ? 'active' : '' }}">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.7" stroke="currentColor" class="lbl-icon"><path stroke-linecap="round" stroke-linejoin="round" d="M2.25 18 9 11.25l4.306 4.306a11.95 11.95 0 0 1 5.814-5.518l2.74-1.22m0 0-5.94-2.281m5.94 2.28-2.28 5.941" /></svg>
                    <span class="lbl-text">{{ __('Analytics') }}</span>
                    @if($analyticsLocked) {!! $lockSvgInline !!} @endif
                </a>
            </div>

            @php
                $lockSvg = '<svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="lock-ic"><path stroke-linecap="round" stroke-linejoin="round" d="M16.5 10.5V6.75a4.5 4.5 0 1 0-9 0v3.75m-.75 11.25h10.5a2.25 2.25 0 0 0 2.25-2.25v-6.75a2.25 2.25 0 0 0-2.25-2.25H6.75a2.25 2.25 0 0 0-2.25 2.25v6.75a2.25 2.25 0 0 0 2.25 2.25Z"/></svg>';
            @endphp

            <div class="ds-sb-group">
                <div class="ds-sb-group-title">{{ __('Finance') }}</div>
                <a href="{{ $payrollLocked ? '#' : route('admin.payrolls') }}"
                   @if($payrollLocked) @click.prevent="$dispatch('feature-lock', { title: 'Payroll Locked', message: 'Payroll Management is an Enterprise Feature. Please upgrade.' })" @else wire:navigate.hover @endif
                   class="ds-sb-link {{ $payrollLocked ? 'locked' : '' }} {{ $isActive(['admin.payrolls']) ? 'active' : '' }}">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.7" stroke="currentColor" class="lbl-icon"><path stroke-linecap="round" stroke-linejoin="round" d="M21 12a2.25 2.25 0 0 0-2.25-2.25H15a3 3 0 1 1-6 0H5.25A2.25 2.25 0 0 0 3 12" /></svg>
                    <span class="lbl-text">{{ __('Payroll') }}</span>
                    @if($payrollLocked) {!! $lockSvg !!} @endif
                </a>
                <a href="{{ $payrollLocked ? '#' : route('admin.payroll.settings') }}"
                   @if($payrollLocked) @click.prevent="$dispatch('feature-lock', { title: 'Payroll Settings Locked', message: 'Payroll Settings is an Enterprise Feature. Please upgrade.' })" @else wire:navigate.hover @endif
                   class="ds-sb-link {{ $payrollLocked ? 'locked' : '' }} {{ $isActive(['*admin.payroll.settings']) ? 'active' : '' }}">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.7" stroke="currentColor" class="lbl-icon"><path stroke-linecap="round" stroke-linejoin="round" d="M9.594 3.94c.09-.542.56-.94 1.11-.94h2.593c.55 0 1.02.398 1.11.94l.213 1.281" /></svg>
                    <span class="lbl-text">{{ __('Payroll settings') }}</span>
                    @if($payrollLocked) {!! $lockSvg !!} @endif
                </a>
                <a href="{{ $payrollLocked ? '#' : route('admin.reimbursements') }}"
                   @if($payrollLocked) @click.prevent="$dispatch('feature-lock', { title: 'Reimbursements Locked', message: 'Reimbursement Management is an Enterprise Feature. Please upgrade.' })" @else wire:navigate.hover @endif
                   class="ds-sb-link {{ $payrollLocked ? 'locked' : '' }} {{ $isActive(['*admin.reimbursements']) ? 'active' : '' }}">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.7" stroke="currentColor" class="lbl-icon"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12h3.75M9 15h3.75M9 18h3.75m3 .75H18a2.25 2.25 0 0 0 2.25-2.25V6.108" /></svg>
                    <span class="lbl-text">{{ __('Reimbursements') }}</span>
                    @if($payrollLocked) {!! $lockSvg !!} @endif
                </a>
                <a href="{{ $payrollLocked ? '#' : route('admin.manage-kasbon') }}"
                   @if($payrollLocked) @click.prevent="$dispatch('feature-lock', { title: 'Cash Advances Locked', message: 'Cash Advance Management is an Enterprise Feature. Please upgrade.' })" @else wire:navigate.hover @endif
                   class="ds-sb-link {{ $payrollLocked ? 'locked' : '' }} {{ $isActive(['*admin.manage-kasbon', '*admin.kasbon']) ? 'active' : '' }}">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.7" stroke="currentColor" class="lbl-icon"><path stroke-linecap="round" stroke-linejoin="round" d="M2.25 18.75a60.07 60.07 0 0 1 15.797 2.101" /></svg>
                    <span class="lbl-text">{{ __('Cash advances') }}</span>
                    @if($payrollLocked) {!! $lockSvg !!} @endif
                </a>
            </div>

            <div class="ds-sb-group">
                <div class="ds-sb-group-title">{{ __('Setup') }}</div>
                <a href="{{ route('admin.masters.division') }}" wire:navigate.hover class="ds-sb-link {{ $isActive(['*admin.masters']) ? 'active' : '' }}">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.7" stroke="currentColor" class="lbl-icon"><path stroke-linecap="round" stroke-linejoin="round" d="M2.25 21h19.5m-18-18v18m10.5-18v18m6-13.5V21M6.75 6.75h.75m-.75 3h.75m-.75 3h.75m3-6h.75m-.75 3h.75m-.75 3h.75M6.75 21v-3.375c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125V21" /></svg>
                    <span class="lbl-text">{{ __('Master data') }}</span>
                </a>
                <a href="{{ route('admin.barcodes') }}" wire:navigate.hover class="ds-sb-link {{ $isActive(['*admin.barcodes']) ? 'active' : '' }}">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.7" stroke="currentColor" class="lbl-icon"><path stroke-linecap="round" stroke-linejoin="round" d="M3.75 4.875c0-.621.504-1.125 1.125-1.125h4.5c.621 0 1.125.504 1.125 1.125v4.5c0 .621-.504 1.125-1.125 1.125h-4.5A1.125 1.125 0 0 1 3.75 9.375v-4.5ZM13.5 4.875c0-.621.504-1.125 1.125-1.125h4.5c.621 0 1.125.504 1.125 1.125v4.5c0 .621-.504 1.125-1.125 1.125h-4.5A1.125 1.125 0 0 1 13.5 9.375v-4.5ZM3.75 14.625c0-.621.504-1.125 1.125-1.125h4.5c.621 0 1.125.504 1.125 1.125v4.5c0 .621-.504 1.125-1.125 1.125h-4.5a1.125 1.125 0 0 1-1.125-1.125v-4.5Z" /></svg>
                    <span class="lbl-text">{{ __('Barcode locations') }}</span>
                </a>
                <a href="{{ route('admin.geofence-locations') }}" wire:navigate.hover class="ds-sb-link {{ $isActive(['*admin.geofence-locations']) ? 'active' : '' }}">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.7" stroke="currentColor" class="lbl-icon"><path stroke-linecap="round" stroke-linejoin="round" d="M12 21s7.5-4.917 7.5-11.25a7.5 7.5 0 1 0-15 0C4.5 16.083 12 21 12 21Z" /><path stroke-linecap="round" stroke-linejoin="round" d="M12 12.75a3 3 0 1 0 0-6 3 3 0 0 0 0 6Z" /></svg>
                    <span class="lbl-text">{{ __('Geofence locations') }}</span>
                </a>
                <a href="{{ $reportingLocked ? '#' : route('admin.import-export.attendances') }}"
                   @if($reportingLocked) @click.prevent="$dispatch('feature-lock', { title: 'Import / Export Locked', message: 'Import and export tools are an Enterprise Feature. Please upgrade.' })" @else wire:navigate.hover @endif
                   class="ds-sb-link {{ $reportingLocked ? 'locked' : '' }} {{ $isActive(['*admin.import-export']) ? 'active' : '' }}">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.7" stroke="currentColor" class="lbl-icon"><path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 0 0 5.25 21h13.5A2.25 2.25 0 0 0 21 18.75V16.5M7.5 7.5 12 3m0 0 4.5 4.5M12 3v13.5" /></svg>
                    <span class="lbl-text">{{ __('Import / Export') }}</span>
                    @if($reportingLocked) {!! $lockSvg !!} @endif
                </a>
                <a href="{{ $auditLocked ? '#' : route('admin.activity-logs') }}"
                   @if($auditLocked) @click.prevent="$dispatch('feature-lock', { title: 'Activity Logs Locked', message: 'Audit logs are an Enterprise Feature. Please upgrade.' })" @else wire:navigate.hover @endif
                   class="ds-sb-link {{ $auditLocked ? 'locked' : '' }} {{ $isActive(['*admin.activity-logs']) ? 'active' : '' }}">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.7" stroke="currentColor" class="lbl-icon"><path stroke-linecap="round" stroke-linejoin="round" d="M3.75 3v11.25A2.25 2.25 0 0 0 6 16.5h2.25M3.75 3h-1.5m1.5 0h16.5" /></svg>
                    <span class="lbl-text">{{ __('Activity logs') }}</span>
                    @if($auditLocked) {!! $lockSvg !!} @endif
                </a>
                @if($user->isSuperadmin)
                <a href="{{ route('admin.settings') }}" wire:navigate.hover class="ds-sb-link {{ $isActive(['*admin.settings']) ? 'active' : '' }}">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.7" stroke="currentColor" class="lbl-icon"><path stroke-linecap="round" stroke-linejoin="round" d="M9.594 3.94c.09-.542.56-.94 1.11-.94h2.593c.55 0 1.02.398 1.11.94l.213 1.281" /></svg>
                    <span class="lbl-text">{{ __('Settings') }}</span>
                </a>
                <a href="{{ route('admin.system-maintenance') }}" wire:navigate.hover class="ds-sb-link {{ $isActive(['*admin.system-maintenance']) ? 'active' : '' }}">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.7" stroke="currentColor" class="lbl-icon"><path stroke-linecap="round" stroke-linejoin="round" d="M11.42 15.17 17.25 21A2.652 2.652 0 0 0 21 17.25l-5.83-5.83M11.42 15.17l2.496-3.03c.317-.384.74-.626 1.208-.766M11.42 15.17l-4.655 5.653a2.548 2.548 0 1 1-3.586-3.586l5.653-4.655" /></svg>
                    <span class="lbl-text">{{ __('Maintenance') }}</span>
                </a>
                @endif
            </div>
        @else
            {{-- EMPLOYEE --}}
            <div class="ds-sb-group">
                <div class="ds-sb-group-title">{{ __('Workspace') }}</div>
                <a href="{{ route('home') }}" wire:navigate.hover class="ds-sb-link {{ $isActive(['home']) ? 'active' : '' }}">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.7" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M3.75 6A2.25 2.25 0 0 1 6 3.75h2.25A2.25 2.25 0 0 1 10.5 6v2.25a2.25 2.25 0 0 1-2.25 2.25H6a2.25 2.25 0 0 1-2.25-2.25V6ZM3.75 15.75A2.25 2.25 0 0 1 6 13.5h2.25a2.25 2.25 0 0 1 2.25 2.25V18a2.25 2.25 0 0 1-2.25 2.25H6A2.25 2.25 0 0 1 3.75 18v-2.25Z" /></svg>
                    <span class="lbl-text">{{ __('Dashboard') }}</span>
                </a>
                <a href="{{ route('my-schedule') }}" wire:navigate.hover class="ds-sb-link {{ $isActive(['my-schedule', 'attendance-history']) ? 'active' : '' }}">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.7" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M6.75 3v2.25M17.25 3v2.25M3 18.75V7.5a2.25 2.25 0 0 1 2.25-2.25h13.5A2.25 2.25 0 0 1 21 7.5v11.25" /></svg>
                    <span class="lbl-text">{{ __('Time Sheet') }}</span>
                </a>
                <a href="{{ route('apply-leave') }}" wire:navigate.hover class="ds-sb-link {{ $isActive(['apply-leave', 'store-leave-request']) ? 'active' : '' }}">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.7" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M2.25 13.5h3.86a2.25 2.25 0 0 1 2.012 1.244l.256.512a2.25 2.25 0 0 0 2.013 1.244h3.218a2.25 2.25 0 0 0 2.013-1.244l.256-.512a2.25 2.25 0 0 1 2.013-1.244h3.859" /></svg>
                    <span class="lbl-text">{{ __('Requests') }}</span>
                </a>
                <a href="{{ route('notifications') }}" wire:navigate.hover class="ds-sb-link {{ $isActive(['notifications']) ? 'active' : '' }}">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.7" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M14.857 17.082a23.848 23.848 0 0 0 5.454-1.31A8.967 8.967 0 0 1 18 9.75V9A6 6 0 0 0 6 9v.75a8.967 8.967 0 0 1-2.312 6.022c1.733.64 3.56 1.085 5.455 1.31m5.714 0a24.255 24.255 0 0 1-5.714 0m5.714 0a3 3 0 1 1-5.714 0" /></svg>
                    <span class="lbl-text">{{ __('Notifications') }}</span>
                </a>
            </div>

            <div class="ds-sb-group">
                <div class="ds-sb-group-title">{{ __('Personal') }}</div>
                <a href="{{ route('my-payslips') }}" wire:navigate.hover class="ds-sb-link {{ $isActive(['my-payslips']) ? 'active' : '' }}">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.7" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M21 12a2.25 2.25 0 0 0-2.25-2.25H15a3 3 0 1 1-6 0H5.25A2.25 2.25 0 0 0 3 12" /></svg>
                    <span class="lbl-text">{{ __('Payslips') }}</span>
                </a>
                <a href="{{ route('attendance-history') }}" wire:navigate.hover class="ds-sb-link {{ $isActive(['attendance-history']) ? 'active' : '' }}">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.7" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M3.75 3v11.25A2.25 2.25 0 0 0 6 16.5h2.25M3.75 3h-1.5m1.5 0h16.5" /></svg>
                    <span class="lbl-text">{{ __('History') }}</span>
                </a>
            </div>
        @endif
    </nav>

    {{-- Footer: user card → opens a popover with Profile / Theme / Sign out --}}
    <div class="ds-sb-foot" x-data="{ pop: false }" @click.outside="pop = false" @keydown.escape.window="pop = false">
        <button type="button" @click="pop = !pop" class="ds-sb-user" aria-haspopup="menu" :aria-expanded="pop ? 'true' : 'false'">
            <div class="av">
                @if($user->profile_photo_url)
                    <img src="{{ $user->profile_photo_url }}" alt="">
                @else
                    {{ $initials }}
                @endif
            </div>
            <div class="meta">
                <div class="nm">{{ $user->name }}</div>
                <div class="role">{{ $isAdmin ? ($user->isSuperadmin ? __('Superadmin') : __('Admin')) : (optional($user->jobTitle)->name ?? __('Employee')) }}</div>
            </div>
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="caret h-4 w-4 text-[var(--ds-ink-3)]" :class="pop ? '-rotate-90' : ''" style="transition: transform 120ms ease;">
                <path stroke-linecap="round" stroke-linejoin="round" d="m8.25 4.5 7.5 7.5-7.5 7.5" />
            </svg>
        </button>

        <div x-show="pop"
             x-transition:enter="transition ease-out duration-120"
             x-transition:enter-start="opacity-0 translate-y-1"
             x-transition:enter-end="opacity-100 translate-y-0"
             class="ds-sb-pop" role="menu" x-cloak>
            <a href="{{ route('profile.show') }}" wire:navigate.hover class="ds-sb-pop-row" role="menuitem" @click="pop = false">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.7" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M15.75 6a3.75 3.75 0 1 1-7.5 0 3.75 3.75 0 0 1 7.5 0ZM4.501 20.118a7.5 7.5 0 0 1 14.998 0"/></svg>
                <span>{{ __('Profile & settings') }}</span>
            </a>
            <button type="button" class="ds-sb-pop-row" role="menuitem"
                    @click="$store.darkMode?.toggle(); pop = false">
                <svg x-show="!$store.darkMode?.on" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.7" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M21.752 15.002A9.72 9.72 0 0 1 18 15.75c-5.385 0-9.75-4.365-9.75-9.75 0-1.33.266-2.597.748-3.752A9.753 9.753 0 0 0 3 11.25C3 16.635 7.365 21 12.75 21a9.753 9.753 0 0 0 9.002-5.998Z"/></svg>
                <svg x-show="$store.darkMode?.on" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.7" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M12 3v2.25m6.364.386-1.591 1.591M21 12h-2.25m-.386 6.364-1.591-1.591M12 18.75V21m-4.773-4.227-1.591 1.591M5.25 12H3m4.227-4.773L5.636 5.636M15.75 12a3.75 3.75 0 1 1-7.5 0 3.75 3.75 0 0 1 7.5 0Z"/></svg>
                <span x-text="$store.darkMode?.on ? '{{ __('Light mode') }}' : '{{ __('Dark mode') }}'"></span>
            </button>
            <div class="ds-sb-pop-divider"></div>
            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <button type="submit" class="ds-sb-pop-row danger" role="menuitem">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.7" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M15.75 9V5.25A2.25 2.25 0 0 0 13.5 3h-6a2.25 2.25 0 0 0-2.25 2.25v13.5A2.25 2.25 0 0 0 7.5 21h6a2.25 2.25 0 0 0 2.25-2.25V15M12 9l-3 3m0 0 3 3m-3-3h12.75"/></svg>
                    <span>{{ __('Sign out') }}</span>
                </button>
            </form>
        </div>
    </div>
</aside>
@endauth
