@php
    $user = auth()->user();
    $isAdmin = $user->isAdmin || $user->isSuperadmin;
    $initials = collect(explode(' ', trim($user->name)))->take(2)->map(fn($p) => mb_substr($p, 0, 1))->implode('');
    $payrollLocked   = \App\Helpers\Editions::payrollLocked();
    $reportingLocked = \App\Helpers\Editions::reportingLocked();
    $auditLocked     = \App\Helpers\Editions::auditLocked();
    $lockBadge = '<span class="lock-overlay" title="Locked"><svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2.2" stroke="currentColor" class="h-3 w-3"><path stroke-linecap="round" stroke-linejoin="round" d="M16.5 10.5V6.75a4.5 4.5 0 1 0-9 0v3.75m-.75 11.25h10.5a2.25 2.25 0 0 0 2.25-2.25v-6.75a2.25 2.25 0 0 0-2.25-2.25H6.75a2.25 2.25 0 0 0-2.25 2.25v6.75a2.25 2.25 0 0 0 2.25 2.25Z"/></svg></span>';
    $lockRowIcon = '<svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="h-3.5 w-3.5 text-[var(--ds-ink-3)] ml-2"><path stroke-linecap="round" stroke-linejoin="round" d="M16.5 10.5V6.75a4.5 4.5 0 1 0-9 0v3.75m-.75 11.25h10.5a2.25 2.25 0 0 0 2.25-2.25v-6.75a2.25 2.25 0 0 0-2.25-2.25H6.75a2.25 2.25 0 0 0-2.25 2.25v6.75a2.25 2.25 0 0 0 2.25 2.25Z"/></svg>';
@endphp

<x-app-layout>
    {{-- ──────────────────────────────────────────────────────────
         MOBILE (<sm) — mobile layout (hub/launcher)
         ────────────────────────────────────────────────────────── --}}
    <div class="sm:hidden">
        @if($isAdmin)
            {{-- Admin: launcher hub --}}
            <div class="ds-slim-head">
                <div>
                    <h1 class="hd-title">{{ __('More') }}</h1>
                    <div class="hd-sub">{{ config('app.name') }}</div>
                </div>
            </div>

            <div class="px-5 pb-4">
                <div class="ds-soft-card ds-soft-card-pad">
                    <div class="flex items-center gap-3.5">
                        <div class="ds-av ds-av-6" style="width: 48px; height: 48px; font-size: 16px;">
                            @if($user->profile_photo_url)
                                <img src="{{ $user->profile_photo_url }}" alt="" class="h-full w-full rounded-full object-cover">
                            @else
                                {{ $initials }}
                            @endif
                        </div>
                        <div class="flex-1 min-w-0">
                            <div class="font-semibold text-[var(--ds-ink)] truncate">{{ $user->name }}</div>
                            <div class="text-xs text-[var(--ds-ink-3)] truncate">{{ $user->isSuperadmin ? __('Superadmin') : __('Admin') }} · {{ config('app.name') }}</div>
                        </div>
                        <a href="#profile-forms" class="ds-btn-secondary" style="padding: 6px 12px; font-size: 12px;">
                            {{ __('Edit') }}
                        </a>
                    </div>
                </div>
            </div>

            <div class="ds-block">
                <div class="ds-sec-head"><span>{{ __('Attendance') }}</span></div>
                <div class="ds-launcher-grid">
                    <a href="{{ route('admin.attendances') }}" wire:navigate class="ds-launcher-tile">
                        <div class="lt-head"><div class="lt-ic"><svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.7" stroke="currentColor" class="h-4 w-4"><path stroke-linecap="round" stroke-linejoin="round" d="M3.75 3v11.25A2.25 2.25 0 0 0 6 16.5h2.25M3.75 3h-1.5m1.5 0h16.5m0 0h1.5m-1.5 0v11.25A2.25 2.25 0 0 1 18 16.5h-2.25m-7.5 0h7.5"/></svg></div></div>
                        <div class="lt-title">{{ __('Daily log') }}</div>
                        <div class="lt-sub">{{ __('All check-ins today') }}</div>
                    </a>
                    <a href="{{ route('admin.schedules') }}" wire:navigate class="ds-launcher-tile">
                        <div class="lt-head"><div class="lt-ic"><svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.7" stroke="currentColor" class="h-4 w-4"><path stroke-linecap="round" stroke-linejoin="round" d="M6.75 3v2.25M17.25 3v2.25M3 18.75V7.5a2.25 2.25 0 0 1 2.25-2.25h13.5A2.25 2.25 0 0 1 21 7.5v11.25"/></svg></div></div>
                        <div class="lt-title">{{ __('Schedules') }}</div>
                        <div class="lt-sub">{{ __('Shift rotations') }}</div>
                    </a>
                    <a href="{{ route('admin.holidays') }}" wire:navigate class="ds-launcher-tile">
                        <div class="lt-head"><div class="lt-ic"><svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.7" stroke="currentColor" class="h-4 w-4"><path stroke-linecap="round" stroke-linejoin="round" d="M11.48 3.499a.562.562 0 0 1 1.04 0l2.125 5.111a.563.563 0 0 0 .475.345l5.518.442c.499.04.701.663.321.988l-4.204 3.602a.563.563 0 0 0-.182.557l1.285 5.385a.562.562 0 0 1-.84.61l-4.725-2.885a.562.562 0 0 0-.586 0L6.982 20.54a.562.562 0 0 1-.84-.61l1.285-5.386a.562.562 0 0 0-.182-.557l-4.204-3.602a.562.562 0 0 1 .321-.988l5.518-.442a.563.563 0 0 0 .475-.345L11.48 3.5Z"/></svg></div></div>
                        <div class="lt-title">{{ __('Holidays') }}</div>
                        <div class="lt-sub">{{ date('Y') }} {{ __('calendar') }}</div>
                    </a>
                    <a href="{{ route('admin.announcements') }}" wire:navigate class="ds-launcher-tile">
                        <div class="lt-head"><div class="lt-ic"><svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.7" stroke="currentColor" class="h-4 w-4"><path stroke-linecap="round" stroke-linejoin="round" d="M10.34 15.84c-.688-.06-1.386-.09-2.09-.09H7.5a4.5 4.5 0 1 1 0-9h.75c.704 0 1.402-.03 2.09-.09m0 9.18c.253.962.584 1.892.985 2.783.247.55.06 1.21-.463 1.511l-.657.38c-.551.318-1.26.117-1.527-.461a20.845 20.845 0 0 1-1.44-4.282m3.102.069a18.03 18.03 0 0 1-.59-4.59c0-1.586.205-3.124.59-4.59m0 9.18a23.848 23.848 0 0 1 8.835 2.535M10.34 6.66a23.847 23.847 0 0 0 8.835-2.535m0 0A23.74 23.74 0 0 0 18.795 3m.38 1.125a23.91 23.91 0 0 1 1.014 5.395m-1.014 8.855c-.118.38-.245.754-.38 1.125m.38-1.125a23.91 23.91 0 0 0 1.014-5.395"/></svg></div></div>
                        <div class="lt-title">{{ __('Announcements') }}</div>
                        <div class="lt-sub">{{ __('Org-wide notices') }}</div>
                    </a>
                </div>
            </div>

            <div class="ds-block">
                <div class="ds-sec-head"><span>{{ __('Finance') }}</span></div>
                <div class="ds-soft-card" x-data>
                    <a href="{{ $payrollLocked ? '#' : route('admin.payrolls') }}"
                       @if($payrollLocked) @click.prevent="$dispatch('feature-lock', { title: 'Payroll Locked', message: 'Payroll is an Enterprise Feature. Please upgrade.' })" @else wire:navigate.hover @endif
                       class="ds-soft-row" style="{{ $payrollLocked ? 'opacity: 0.7;' : '' }}">
                        <div class="ds-tile ds-tile-acc">
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.7" stroke="currentColor" class="h-4 w-4"><path stroke-linecap="round" stroke-linejoin="round" d="M21 12a2.25 2.25 0 0 0-2.25-2.25H15a3 3 0 1 1-6 0H5.25A2.25 2.25 0 0 0 3 12"/></svg>
                        </div>
                        <div class="row-main">
                            <div class="row-title">{{ __('Payroll') }}</div>
                            <div class="row-sub">{{ $payrollLocked ? __('Enterprise feature') : __('Run & history') }}</div>
                        </div>
                        @if($payrollLocked) {!! $lockRowIcon !!} @endif
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.7" stroke="currentColor" class="h-4 w-4 arrow"><path stroke-linecap="round" stroke-linejoin="round" d="m8.25 4.5 7.5 7.5-7.5 7.5"/></svg>
                    </a>
                    <a href="{{ $payrollLocked ? '#' : route('admin.reimbursements') }}"
                       @if($payrollLocked) @click.prevent="$dispatch('feature-lock', { title: 'Reimbursements Locked', message: 'Reimbursements is an Enterprise Feature. Please upgrade.' })" @else wire:navigate.hover @endif
                       class="ds-soft-row" style="{{ $payrollLocked ? 'opacity: 0.7;' : '' }}">
                        <div class="ds-tile">
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.7" stroke="currentColor" class="h-4 w-4"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12h3.75M9 15h3.75M9 18h3.75"/></svg>
                        </div>
                        <div class="row-main">
                            <div class="row-title">{{ __('Reimbursements') }}</div>
                            <div class="row-sub">{{ $payrollLocked ? __('Enterprise feature') : __('Claims & receipts') }}</div>
                        </div>
                        @if($payrollLocked) {!! $lockRowIcon !!} @endif
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.7" stroke="currentColor" class="h-4 w-4 arrow"><path stroke-linecap="round" stroke-linejoin="round" d="m8.25 4.5 7.5 7.5-7.5 7.5"/></svg>
                    </a>
                    <a href="{{ $payrollLocked ? '#' : route('admin.manage-kasbon') }}"
                       @if($payrollLocked) @click.prevent="$dispatch('feature-lock', { title: 'Cash Advances Locked', message: 'Cash advances are an Enterprise Feature. Please upgrade.' })" @else wire:navigate.hover @endif
                       class="ds-soft-row" style="{{ $payrollLocked ? 'opacity: 0.7;' : '' }}">
                        <div class="ds-tile">
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.7" stroke="currentColor" class="h-4 w-4"><path stroke-linecap="round" stroke-linejoin="round" d="M2.25 18.75a60.07 60.07 0 0 1 15.797 2.101"/></svg>
                        </div>
                        <div class="row-main">
                            <div class="row-title">{{ __('Cash advances') }}</div>
                            <div class="row-sub">{{ $payrollLocked ? __('Enterprise feature') : __('Outstanding & approvals') }}</div>
                        </div>
                        @if($payrollLocked) {!! $lockRowIcon !!} @endif
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.7" stroke="currentColor" class="h-4 w-4 arrow"><path stroke-linecap="round" stroke-linejoin="round" d="m8.25 4.5 7.5 7.5-7.5 7.5"/></svg>
                    </a>
                </div>
            </div>

            <div class="ds-block">
                <div class="ds-sec-head"><span>{{ __('Setup') }}</span></div>
                <div class="ds-soft-card">
                    <a href="{{ route('admin.masters.division') }}" wire:navigate class="ds-soft-row">
                        <div class="ds-tile">
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.7" stroke="currentColor" class="h-4 w-4"><path stroke-linecap="round" stroke-linejoin="round" d="M2.25 21h19.5m-18-18v18m10.5-18v18m6-13.5V21M6.75 6.75h.75m-.75 3h.75m-.75 3h.75m3-6h.75m-.75 3h.75m-.75 3h.75M6.75 21v-3.375c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125V21"/></svg>
                        </div>
                        <div class="row-main">
                            <div class="row-title">{{ __('Master data') }}</div>
                            <div class="row-sub">{{ __('Divisions · Job titles · Shifts') }}</div>
                        </div>
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.7" stroke="currentColor" class="h-4 w-4 arrow"><path stroke-linecap="round" stroke-linejoin="round" d="m8.25 4.5 7.5 7.5-7.5 7.5"/></svg>
                    </a>
                    <a href="{{ $reportingLocked ? '#' : route('admin.analytics') }}"
                       @if($reportingLocked) @click.prevent="$dispatch('feature-lock', { title: 'Analytics Locked', message: 'Reports & Analytics is an Enterprise Feature. Please upgrade.' })" @else wire:navigate.hover @endif
                       class="ds-soft-row" style="{{ $reportingLocked ? 'opacity: 0.7;' : '' }}">
                        <div class="ds-tile">
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.7" stroke="currentColor" class="h-4 w-4"><path stroke-linecap="round" stroke-linejoin="round" d="M2.25 18 9 11.25l4.306 4.306a11.95 11.95 0 0 1 5.814-5.518l2.74-1.22m0 0-5.94-2.281m5.94 2.28-2.28 5.941"/></svg>
                        </div>
                        <div class="row-main">
                            <div class="row-title">{{ __('Reports & analytics') }}</div>
                            <div class="row-sub">{{ $reportingLocked ? __('Enterprise feature') : __('Trends & insights') }}</div>
                        </div>
                        @if($reportingLocked) {!! $lockRowIcon !!} @endif
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.7" stroke="currentColor" class="h-4 w-4 arrow"><path stroke-linecap="round" stroke-linejoin="round" d="m8.25 4.5 7.5 7.5-7.5 7.5"/></svg>
                    </a>
                    <a href="{{ $auditLocked ? '#' : route('admin.activity-logs') }}"
                       @if($auditLocked) @click.prevent="$dispatch('feature-lock', { title: 'Activity Logs Locked', message: 'Audit logs are an Enterprise Feature. Please upgrade.' })" @else wire:navigate.hover @endif
                       class="ds-soft-row" style="{{ $auditLocked ? 'opacity: 0.7;' : '' }}">
                        <div class="ds-tile">
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.7" stroke="currentColor" class="h-4 w-4"><path stroke-linecap="round" stroke-linejoin="round" d="M3.75 3v11.25A2.25 2.25 0 0 0 6 16.5h2.25M3.75 3h-1.5m1.5 0h16.5"/></svg>
                        </div>
                        <div class="row-main">
                            <div class="row-title">{{ __('Activity logs') }}</div>
                            <div class="row-sub">{{ $auditLocked ? __('Enterprise feature') : __('30-day audit trail') }}</div>
                        </div>
                        @if($auditLocked) {!! $lockRowIcon !!} @endif
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.7" stroke="currentColor" class="h-4 w-4 arrow"><path stroke-linecap="round" stroke-linejoin="round" d="m8.25 4.5 7.5 7.5-7.5 7.5"/></svg>
                    </a>
                    @if($user->isSuperadmin)
                    <a href="{{ route('admin.settings') }}" wire:navigate class="ds-soft-row">
                        <div class="ds-tile">
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.7" stroke="currentColor" class="h-4 w-4"><path stroke-linecap="round" stroke-linejoin="round" d="M9.594 3.94c.09-.542.56-.94 1.11-.94h2.593c.55 0 1.02.398 1.11.94l.213 1.281c.063.374.313.686.645.87.074.04.147.083.22.127.325.196.72.257 1.075.124l1.217-.456a1.125 1.125 0 0 1 1.37.49l1.296 2.247a1.125 1.125 0 0 1-.26 1.431l-1.003.827c-.293.241-.438.613-.43.992a7.723 7.723 0 0 1 0 .255c-.008.378.137.75.43.991l1.004.827c.424.35.534.955.26 1.43l-1.298 2.247a1.125 1.125 0 0 1-1.369.491l-1.217-.456c-.355-.133-.75-.072-1.076.124a6.47 6.47 0 0 1-.22.128c-.331.183-.581.495-.644.869l-.213 1.281c-.09.543-.56.94-1.11.94h-2.594c-.55 0-1.019-.398-1.11-.94l-.213-1.281c-.062-.374-.312-.686-.644-.87a6.52 6.52 0 0 1-.22-.127c-.325-.196-.72-.257-1.076-.124l-1.217.456a1.125 1.125 0 0 1-1.369-.49l-1.297-2.247a1.125 1.125 0 0 1 .26-1.431l1.004-.827c.292-.24.437-.613.43-.991a6.932 6.932 0 0 1 0-.255c.007-.38-.138-.751-.43-.992l-1.004-.827a1.125 1.125 0 0 1-.26-1.43l1.297-2.247a1.125 1.125 0 0 1 1.37-.491l1.216.456c.356.133.751.072 1.076-.124.072-.044.146-.087.22-.128.332-.183.582-.495.644-.869l.214-1.28Z"/></svg>
                        </div>
                        <div class="row-main">
                            <div class="row-title">{{ __('Settings') }}</div>
                            <div class="row-sub">{{ __('Org · billing · integrations') }}</div>
                        </div>
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.7" stroke="currentColor" class="h-4 w-4 arrow"><path stroke-linecap="round" stroke-linejoin="round" d="m8.25 4.5 7.5 7.5-7.5 7.5"/></svg>
                    </a>
                    @endif
                    <form method="POST" action="{{ route('logout') }}" x-data>
                        @csrf
                        <button type="submit" class="ds-soft-row w-full text-left" style="background: transparent; border: 0; cursor: pointer;">
                            <div class="ds-tile ds-tile-bad">
                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.7" stroke="currentColor" class="h-4 w-4"><path stroke-linecap="round" stroke-linejoin="round" d="M15.75 9V5.25A2.25 2.25 0 0 0 13.5 3h-6a2.25 2.25 0 0 0-2.25 2.25v13.5A2.25 2.25 0 0 0 7.5 21h6a2.25 2.25 0 0 0 2.25-2.25V15M12 9l-3 3m0 0 3 3m-3-3h12.75"/></svg>
                            </div>
                            <div class="row-main">
                                <div class="row-title" style="color: var(--ds-bad-ink);">{{ __('Sign out') }}</div>
                            </div>
                        </button>
                    </form>
                </div>
            </div>

        @else
            {{-- Employee profile hub --}}
            <div class="ds-slim-head">
                <div>
                    <h1 class="hd-title">{{ __('Profile') }}</h1>
                </div>
            </div>

            <div class="px-5 pb-5">
                <div style="background: linear-gradient(135deg, var(--ds-accent-soft), var(--ds-bg-2)); border: 1px solid var(--ds-line); border-radius: 22px; padding: 20px;">
                    <div class="flex items-center gap-3.5">
                        <div class="ds-av ds-av-3" style="width: 56px; height: 56px; font-size: 18px;">
                            @if($user->profile_photo_url)
                                <img src="{{ $user->profile_photo_url }}" alt="" class="h-full w-full rounded-full object-cover">
                            @else
                                {{ $initials }}
                            @endif
                        </div>
                        <div class="flex-1 min-w-0">
                            <div style="font-family: 'Source Serif 4', Georgia, serif; font-size: 20px; font-weight: 500; letter-spacing: -0.01em; color: var(--ds-ink);">{{ $user->name }}</div>
                            <div style="font-size: 12.5px; color: var(--ds-ink-2);">{{ $user->jobTitle->name ?? __('Employee') }}{{ $user->division ? ' · ' . (json_decode($user->division)->name ?? '') : '' }}</div>
                            @if($user->nip)
                                <div style="font-family: 'JetBrains Mono', ui-monospace, monospace; font-size: 11px; color: var(--ds-ink-3); margin-top: 2px;">{{ $user->nip }}{{ $user->cnic ? ' · CNIC ' . substr($user->cnic, 0, 5) . '-•••••••-•' : '' }}</div>
                            @endif
                        </div>
                    </div>
                </div>
            </div>

            <div class="ds-block">
                <div class="ds-soft-card">
                    <a href="{{ route('my-payslips') }}" wire:navigate class="ds-soft-row">
                        <div class="ds-tile"><svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.7" stroke="currentColor" class="h-4 w-4"><path stroke-linecap="round" stroke-linejoin="round" d="M21 12a2.25 2.25 0 0 0-2.25-2.25H15a3 3 0 1 1-6 0H5.25A2.25 2.25 0 0 0 3 12"/></svg></div>
                        <div class="row-main">
                            <div class="row-title">{{ __('Payslips') }}</div>
                            <div class="row-sub">{{ __('View & download') }}</div>
                        </div>
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.7" stroke="currentColor" class="h-4 w-4 arrow"><path stroke-linecap="round" stroke-linejoin="round" d="m8.25 4.5 7.5 7.5-7.5 7.5"/></svg>
                    </a>
                    <a href="{{ route('attendance-history') }}" wire:navigate class="ds-soft-row">
                        <div class="ds-tile"><svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.7" stroke="currentColor" class="h-4 w-4"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12h6m-6 4h6m2 5H7a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h5.586a1 1 0 0 1 .707.293l5.414 5.414a1 1 0 0 1 .293.707V19a2 2 0 0 1-2 2Z"/></svg></div>
                        <div class="row-main">
                            <div class="row-title">{{ __('Attendance history') }}</div>
                            <div class="row-sub">{{ __('Past clock-ins') }}</div>
                        </div>
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.7" stroke="currentColor" class="h-4 w-4 arrow"><path stroke-linecap="round" stroke-linejoin="round" d="m8.25 4.5 7.5 7.5-7.5 7.5"/></svg>
                    </a>
                    <a href="{{ route('apply-leave') }}" wire:navigate class="ds-soft-row">
                        <div class="ds-tile"><svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.7" stroke="currentColor" class="h-4 w-4"><path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 0 0-3.375-3.375h-1.5A1.125 1.125 0 0 1 13.5 7.125v-1.5a3.375 3.375 0 0 0-3.375-3.375H8.25m2.25 0H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 0 0-9-9Z"/></svg></div>
                        <div class="row-main">
                            <div class="row-title">{{ __('My requests') }}</div>
                            <div class="row-sub">{{ __('Leave & overtime') }}</div>
                        </div>
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.7" stroke="currentColor" class="h-4 w-4 arrow"><path stroke-linecap="round" stroke-linejoin="round" d="m8.25 4.5 7.5 7.5-7.5 7.5"/></svg>
                    </a>
                    @if(\Illuminate\Support\Facades\Route::has('face.enrollment'))
                    <a href="{{ route('face.enrollment') }}" wire:navigate class="ds-soft-row">
                        <div class="ds-tile"><svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.7" stroke="currentColor" class="h-4 w-4"><path stroke-linecap="round" stroke-linejoin="round" d="M9 9.75A.75.75 0 0 1 9.75 9h.008a.75.75 0 0 1 .75.75v.008a.75.75 0 0 1-.75.75H9.75a.75.75 0 0 1-.75-.75V9.75Zm5.25-.75a.75.75 0 0 0-.75.75v.008c0 .414.336.75.75.75h.008a.75.75 0 0 0 .75-.75V9.75a.75.75 0 0 0-.75-.75h-.008Zm-5.25 4.5a.75.75 0 0 1 .75-.75h4.5a.75.75 0 0 1 0 1.5h-4.5a.75.75 0 0 1-.75-.75ZM12 21a9 9 0 1 0 0-18 9 9 0 0 0 0 18Z"/></svg></div>
                        <div class="row-main">
                            <div class="row-title">{{ __('Face ID') }}</div>
                            <div class="row-sub">{{ $user->hasFaceRegistered() ? __('Enrolled') : __('Not enrolled') }}</div>
                        </div>
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.7" stroke="currentColor" class="h-4 w-4 arrow"><path stroke-linecap="round" stroke-linejoin="round" d="m8.25 4.5 7.5 7.5-7.5 7.5"/></svg>
                    </a>
                    @endif
                </div>
            </div>

            <div class="ds-block">
                <div class="ds-soft-card">
                    <a href="#profile-forms" class="ds-soft-row">
                        <div class="ds-tile"><svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.7" stroke="currentColor" class="h-4 w-4"><path stroke-linecap="round" stroke-linejoin="round" d="M15.75 6a3.75 3.75 0 1 1-7.5 0 3.75 3.75 0 0 1 7.5 0ZM4.501 20.118a7.5 7.5 0 0 1 14.998 0"/></svg></div>
                        <div class="row-main"><div class="row-title">{{ __('Personal info') }}</div></div>
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.7" stroke="currentColor" class="h-4 w-4 arrow"><path stroke-linecap="round" stroke-linejoin="round" d="m8.25 4.5 7.5 7.5-7.5 7.5"/></svg>
                    </a>
                    <a href="#profile-forms" class="ds-soft-row">
                        <div class="ds-tile"><svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.7" stroke="currentColor" class="h-4 w-4"><path stroke-linecap="round" stroke-linejoin="round" d="M9.594 3.94c.09-.542.56-.94 1.11-.94h2.593c.55 0 1.02.398 1.11.94l.213 1.281"/></svg></div>
                        <div class="row-main"><div class="row-title">{{ __('Settings & security') }}</div></div>
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.7" stroke="currentColor" class="h-4 w-4 arrow"><path stroke-linecap="round" stroke-linejoin="round" d="m8.25 4.5 7.5 7.5-7.5 7.5"/></svg>
                    </a>
                    <form method="POST" action="{{ route('logout') }}" x-data>
                        @csrf
                        <button type="submit" class="ds-soft-row w-full text-left" style="background: transparent; border: 0; cursor: pointer;">
                            <div class="ds-tile ds-tile-bad">
                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.7" stroke="currentColor" class="h-4 w-4"><path stroke-linecap="round" stroke-linejoin="round" d="M15.75 9V5.25A2.25 2.25 0 0 0 13.5 3h-6a2.25 2.25 0 0 0-2.25 2.25v13.5A2.25 2.25 0 0 0 7.5 21h6a2.25 2.25 0 0 0 2.25-2.25V15M12 9l-3 3m0 0 3 3m-3-3h12.75"/></svg>
                            </div>
                            <div class="row-main">
                                <div class="row-title" style="color: var(--ds-bad-ink);">{{ __('Sign out') }}</div>
                            </div>
                        </button>
                    </form>
                </div>
            </div>
        @endif

        {{-- Anchor: profile forms --}}
        <div id="profile-forms" class="ds-block">
            <div class="ds-sec-head"><span>{{ __('Account') }}</span></div>
            <div class="space-y-4">
                @if (Laravel\Fortify\Features::canUpdateProfileInformation())
                    @livewire('profile.update-profile-information-form')
                @endif

                @if (Laravel\Fortify\Features::enabled(Laravel\Fortify\Features::updatePasswords()))
                    @livewire('profile.update-password-form')
                @endif

                @if (Laravel\Fortify\Features::canManageTwoFactorAuthentication())
                    @livewire('profile.two-factor-authentication-form')
                @endif

                @livewire('profile.logout-other-browser-sessions-form')

                @if (Laravel\Jetstream\Jetstream::hasAccountDeletionFeatures())
                    @livewire('profile.delete-user-form')
                @endif
            </div>
        </div>
    </div>

    {{-- ──────────────────────────────────────────────────────────
         DESKTOP (≥sm) — original profile/settings forms
         ────────────────────────────────────────────────────────── --}}
    <div class="hidden sm:block">
        <div class="py-6 lg:py-12">
            <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
                <div class="flex items-center gap-3 mb-6 lg:mb-8">
                    <x-secondary-button href="{{ url()->previous() }}" class="!rounded-xl !px-3 !py-2 border-gray-200 dark:border-gray-600 bg-white hover:bg-gray-50 dark:bg-gray-800 dark:hover:bg-gray-700">
                        <x-heroicon-o-arrow-left class="h-4 w-4 text-gray-500 dark:text-gray-300" />
                    </x-secondary-button>
                    <h2 class="text-2xl font-bold text-gray-900 dark:text-gray-100 flex items-center gap-2">
                        <span class="p-1.5 bg-primary-50 text-primary-600 dark:bg-primary-900/50 dark:text-primary-400 rounded-lg">👤</span>
                        {{ __('Profile') }}
                    </h2>
                </div>

                <div class="space-y-6">
                    @if (Laravel\Fortify\Features::canUpdateProfileInformation())
                        @livewire('profile.update-profile-information-form')
                    @endif

                    @if (Laravel\Fortify\Features::enabled(Laravel\Fortify\Features::updatePasswords()))
                        @livewire('profile.update-password-form')
                    @endif

                    @if (Laravel\Fortify\Features::canManageTwoFactorAuthentication())
                        @livewire('profile.two-factor-authentication-form')
                    @endif

                    @livewire('profile.logout-other-browser-sessions-form')

                    @if (Laravel\Jetstream\Jetstream::hasAccountDeletionFeatures())
                        @livewire('profile.delete-user-form')
                    @endif
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
