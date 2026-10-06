@auth
    @php
        $user = auth()->user();
        $isAdmin = $user->isAdmin || $user->isSuperadmin;
        $route = request()->route()?->getName() ?? '';
        $payrollLocked = \App\Helpers\Editions::payrollLocked();
        $reportingLocked = \App\Helpers\Editions::reportingLocked();
        $auditLocked = \App\Helpers\Editions::auditLocked();
        $safe = fn (string $name) => \Illuminate\Support\Facades\Route::has($name) ? route($name) : '#';

        $primaryItems = $isAdmin
            ? [
                ['label' => __('Dashboard'), 'route' => 'admin.dashboard', 'icon' => 'grid', 'active' => str_starts_with($route, 'admin.dashboard')],
                ['label' => __('Time Sheet'), 'route' => 'admin.attendances', 'icon' => 'calendar', 'active' => str_starts_with($route, 'admin.attendances') || str_starts_with($route, 'admin.schedules')],
                ['label' => __('Requests'), 'route' => 'admin.leaves', 'icon' => 'inbox', 'active' => str_starts_with($route, 'admin.leaves') || str_starts_with($route, 'admin.overtime')],
                ['label' => __('Alerts'), 'route' => 'notifications', 'icon' => 'bell', 'active' => $route === 'notifications'],
            ]
            : [
                ['label' => __('Dashboard'), 'route' => 'home', 'icon' => 'grid', 'active' => $route === 'home'],
                ['label' => __('Time Sheet'), 'route' => 'my-schedule', 'icon' => 'calendar', 'active' => in_array($route, ['my-schedule', 'attendance-history'])],
                ['label' => __('Requests'), 'route' => 'apply-leave', 'icon' => 'inbox', 'active' => str_starts_with($route, 'apply-leave') || str_starts_with($route, 'store-leave') || $route === 'overtime' || $route === 'reimbursement'],
                ['label' => __('Alerts'), 'route' => 'notifications', 'icon' => 'bell', 'active' => $route === 'notifications'],
            ];

        $moreActive = ! collect($primaryItems)->contains(fn ($item) => $item['active']);

        $moreSections = $isAdmin
            ? [
                __('Workspace') => [
                    ['label' => __('Quick Find'), 'hint' => __('Search'), 'icon' => 'search', 'action' => 'quick-find'],
                    ['label' => __('Staff'), 'hint' => __('Employees'), 'icon' => 'users', 'url' => $safe('admin.employees'), 'active' => str_starts_with($route, 'admin.employees')],
                    ['label' => __('Profile'), 'hint' => $user->name, 'icon' => 'user', 'url' => $safe('profile.show'), 'active' => $route === 'profile.show'],
                ],
                __('Attendance') => [
                    ['label' => __('Overtime'), 'hint' => __('Approvals'), 'icon' => 'inbox', 'url' => $safe('admin.overtime'), 'active' => str_starts_with($route, 'admin.overtime')],
                    ['label' => __('Holidays'), 'hint' => __('Calendar'), 'icon' => 'calendar', 'url' => $safe('admin.holidays'), 'active' => str_starts_with($route, 'admin.holidays')],
                    ['label' => __('Announcements'), 'hint' => __('Notices'), 'icon' => 'bell', 'url' => $safe('admin.announcements'), 'active' => str_starts_with($route, 'admin.announcements')],
                    ['label' => __('Analytics'), 'hint' => $reportingLocked ? __('Enterprise') : __('Reports'), 'icon' => 'chart', 'url' => $safe('admin.analytics'), 'active' => str_starts_with($route, 'admin.analytics'), 'locked' => $reportingLocked, 'lockTitle' => __('Analytics Locked'), 'lockMessage' => __('Advanced Analytics is an Enterprise Feature. Please upgrade.')],
                ],
                __('Finance') => [
                    ['label' => __('Payroll'), 'hint' => $payrollLocked ? __('Enterprise') : __('Run payroll'), 'icon' => 'receipt', 'url' => $safe('admin.payrolls'), 'active' => str_starts_with($route, 'admin.payrolls'), 'locked' => $payrollLocked, 'lockTitle' => __('Payroll Locked'), 'lockMessage' => __('Payroll Management is an Enterprise Feature. Please upgrade.')],
                    ['label' => __('Reimbursements'), 'hint' => $payrollLocked ? __('Enterprise') : __('Claims'), 'icon' => 'receipt', 'url' => $safe('admin.reimbursements'), 'active' => str_starts_with($route, 'admin.reimbursements'), 'locked' => $payrollLocked, 'lockTitle' => __('Reimbursements Locked'), 'lockMessage' => __('Reimbursement Management is an Enterprise Feature. Please upgrade.')],
                    ['label' => __('Cash advances'), 'hint' => $payrollLocked ? __('Enterprise') : __('Outstanding'), 'icon' => 'receipt', 'url' => $safe('admin.manage-kasbon'), 'active' => str_starts_with($route, 'admin.manage-kasbon'), 'locked' => $payrollLocked, 'lockTitle' => __('Cash Advances Locked'), 'lockMessage' => __('Cash Advance Management is an Enterprise Feature. Please upgrade.')],
                ],
                __('Setup') => [
                    ['label' => __('Master data'), 'hint' => __('Divisions, jobs, shifts'), 'icon' => 'grid', 'url' => $safe('admin.masters.division'), 'active' => str_starts_with($route, 'admin.masters')],
                    ['label' => __('Barcode locations'), 'hint' => __('Check-in points'), 'icon' => 'scan', 'url' => $safe('admin.barcodes'), 'active' => str_starts_with($route, 'admin.barcodes')],
                    ['label' => __('Geofence locations'), 'hint' => __('GPS check-in areas'), 'icon' => 'pin', 'url' => $safe('admin.geofence-locations'), 'active' => str_starts_with($route, 'admin.geofence-locations')],
                    ['label' => __('Import / Export'), 'hint' => $reportingLocked ? __('Enterprise') : __('Employees, attendance'), 'icon' => 'receipt', 'url' => $safe('admin.import-export.attendances'), 'active' => str_starts_with($route, 'admin.import-export'), 'locked' => $reportingLocked, 'lockTitle' => __('Import / Export Locked'), 'lockMessage' => __('Import and export tools are an Enterprise Feature. Please upgrade.')],
                    ['label' => __('Activity logs'), 'hint' => $auditLocked ? __('Enterprise') : __('Audit'), 'icon' => 'chart', 'url' => $safe('admin.activity-logs'), 'active' => str_starts_with($route, 'admin.activity-logs'), 'locked' => $auditLocked, 'lockTitle' => __('Activity Logs Locked'), 'lockMessage' => __('Audit logs are an Enterprise Feature. Please upgrade.')],
                    ['label' => __('Settings'), 'hint' => __('App setup'), 'icon' => 'settings', 'url' => $safe('admin.settings'), 'active' => str_starts_with($route, 'admin.settings'), 'show' => $user->isSuperadmin],
                    ['label' => __('Maintenance'), 'hint' => __('System tools'), 'icon' => 'settings', 'url' => $safe('admin.system-maintenance'), 'active' => str_starts_with($route, 'admin.system-maintenance'), 'show' => $user->isSuperadmin],
                ],
            ]
            : [
                __('Workspace') => [
                    ['label' => __('Quick Find'), 'hint' => __('Search'), 'icon' => 'search', 'action' => 'quick-find'],
                    ['label' => __('Profile'), 'hint' => $user->name, 'icon' => 'user', 'url' => $safe('profile.show'), 'active' => $route === 'profile.show'],
                    ['label' => __('Attendance history'), 'hint' => __('Past clock-ins'), 'icon' => 'calendar', 'url' => $safe('attendance-history'), 'active' => $route === 'attendance-history'],
                    ['label' => __('Payslips'), 'hint' => __('View & download'), 'icon' => 'receipt', 'url' => $safe('my-payslips'), 'active' => $route === 'my-payslips'],
                ],
                __('Requests') => [
                    ['label' => __('Overtime'), 'hint' => __('Extra hours'), 'icon' => 'inbox', 'url' => $safe('overtime'), 'active' => $route === 'overtime'],
                    ['label' => __('Reimbursement'), 'hint' => __('Claims'), 'icon' => 'receipt', 'url' => $safe('reimbursement'), 'active' => $route === 'reimbursement'],
                    ['label' => __('Cash advances'), 'hint' => __('My advances'), 'icon' => 'receipt', 'url' => $safe('my-kasbon'), 'active' => $route === 'my-kasbon'],
                    ['label' => __('Team approvals'), 'hint' => __('Subordinates'), 'icon' => 'users', 'url' => $safe('approvals'), 'active' => str_starts_with($route, 'approvals'), 'show' => $user->subordinates->isNotEmpty()],
                ],
            ];
    @endphp

    <div x-data="{ moreOpen: false }" x-on:keydown.escape.window="moreOpen = false">
        <nav aria-label="{{ __('Primary navigation') }}" class="ds-tabbar sm:hidden">
            @foreach($primaryItems as $item)
                @php $url = \Illuminate\Support\Facades\Route::has($item['route']) ? route($item['route']) : '#'; @endphp
                <a href="{{ $url }}"
                   wire:navigate.hover
                   aria-current="{{ $item['active'] ? 'page' : 'false' }}"
                   class="ds-tab {{ $item['active'] ? 'active' : '' }}">
                    <span class="inline-flex h-6 w-6 items-center justify-center">
                        @include('components.mobile-bottom-nav-icon', ['icon' => $item['icon']])
                    </span>
                    <span>{{ $item['label'] }}</span>
                </a>
            @endforeach

            <button type="button"
                    class="ds-tab {{ $moreActive ? 'active' : '' }}"
                    aria-controls="mobile-more-menu"
                    :aria-expanded="moreOpen ? 'true' : 'false'"
                    @click="moreOpen = true">
                <span class="inline-flex h-6 w-6 items-center justify-center">
                    @include('components.mobile-bottom-nav-icon', ['icon' => 'menu'])
                </span>
                <span>{{ __('More') }}</span>
            </button>
        </nav>

        <div class="ds-more-backdrop sm:hidden"
             x-show="moreOpen"
             x-cloak
             x-transition.opacity
             @click.self="moreOpen = false">
            <section id="mobile-more-menu"
                     class="ds-more-panel"
                     role="dialog"
                     aria-modal="true"
                     aria-labelledby="mobile-more-title"
                     x-show="moreOpen"
                     x-transition:enter="transition ease-out duration-180"
                     x-transition:enter-start="translate-y-full"
                     x-transition:enter-end="translate-y-0"
                     x-transition:leave="transition ease-in duration-140"
                     x-transition:leave-start="translate-y-0"
                     x-transition:leave-end="translate-y-full">
                <div class="ds-more-grabber" aria-hidden="true"></div>

                <div class="ds-more-head">
                    <div>
                        <div id="mobile-more-title" class="ds-more-title">{{ __('More') }}</div>
                        <div class="ds-more-sub">{{ $isAdmin ? ($user->isSuperadmin ? __('Superadmin') : __('Admin')) : (optional($user->jobTitle)->name ?? __('Employee')) }}</div>
                    </div>
                    <button type="button" class="ds-more-close" @click="moreOpen = false" aria-label="{{ __('Close menu') }}">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor" class="h-5 w-5">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>

                <div class="ds-more-content">
                    @foreach($moreSections as $section => $items)
                        @php
                            $visibleItems = collect($items)->filter(fn ($item) => $item['show'] ?? true);
                        @endphp
                        @if($visibleItems->isNotEmpty())
                            <div class="ds-more-section">
                                <div class="ds-more-section-title">{{ $section }}</div>
                                <div class="ds-more-list">
                                    @foreach($visibleItems as $item)
                                        @php
                                            $locked = $item['locked'] ?? false;
                                            $active = $item['active'] ?? false;
                                            $title = \Illuminate\Support\Js::from($item['lockTitle'] ?? __('Locked'));
                                            $message = \Illuminate\Support\Js::from($item['lockMessage'] ?? __('This feature is not available.'));
                                        @endphp

                                        @if(($item['action'] ?? null) === 'quick-find')
                                            <button type="button" class="ds-more-row" @click="moreOpen = false; window.dispatchEvent(new CustomEvent('open-quick-find'))">
                                                <span class="ds-more-ic">@include('components.mobile-bottom-nav-icon', ['icon' => $item['icon']])</span>
                                                <span class="ds-more-copy">
                                                    <span class="ds-more-label">{{ $item['label'] }}</span>
                                                    <span class="ds-more-hint">{{ $item['hint'] }}</span>
                                                </span>
                                            </button>
                                        @elseif($locked)
                                            <button type="button" class="ds-more-row is-locked" @click.prevent="$dispatch('feature-lock', { title: {{ $title }}, message: {{ $message }} })">
                                                <span class="ds-more-ic">@include('components.mobile-bottom-nav-icon', ['icon' => $item['icon']])</span>
                                                <span class="ds-more-copy">
                                                    <span class="ds-more-label">{{ $item['label'] }}</span>
                                                    <span class="ds-more-hint">{{ $item['hint'] }}</span>
                                                </span>
                                                <span class="ds-more-lock" aria-hidden="true">
                                                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="h-4 w-4">
                                                        <path stroke-linecap="round" stroke-linejoin="round" d="M16.5 10.5V6.75a4.5 4.5 0 1 0-9 0v3.75m-.75 11.25h10.5a2.25 2.25 0 0 0 2.25-2.25v-6.75a2.25 2.25 0 0 0-2.25-2.25H6.75a2.25 2.25 0 0 0-2.25 2.25v6.75a2.25 2.25 0 0 0 2.25 2.25Z" />
                                                    </svg>
                                                </span>
                                            </button>
                                        @else
                                            <a href="{{ $item['url'] ?? '#' }}" wire:navigate.hover class="ds-more-row {{ $active ? 'active' : '' }}" @click="moreOpen = false">
                                                <span class="ds-more-ic">@include('components.mobile-bottom-nav-icon', ['icon' => $item['icon']])</span>
                                                <span class="ds-more-copy">
                                                    <span class="ds-more-label">{{ $item['label'] }}</span>
                                                    <span class="ds-more-hint">{{ $item['hint'] }}</span>
                                                </span>
                                                <span class="ds-more-arrow" aria-hidden="true">
                                                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor" class="h-4 w-4">
                                                        <path stroke-linecap="round" stroke-linejoin="round" d="m8.25 4.5 7.5 7.5-7.5 7.5" />
                                                    </svg>
                                                </span>
                                            </a>
                                        @endif
                                    @endforeach
                                </div>
                            </div>
                        @endif
                    @endforeach

                    <div class="ds-more-section">
                        <div class="ds-more-section-title">{{ __('Account') }}</div>
                        <div class="ds-more-list">
                            <button type="button" class="ds-more-row" @click="$store.darkMode?.toggle(); moreOpen = false">
                                <span class="ds-more-ic">@include('components.mobile-bottom-nav-icon', ['icon' => 'settings'])</span>
                                <span class="ds-more-copy">
                                    <span class="ds-more-label" x-text="$store.darkMode?.on ? '{{ __('Light mode') }}' : '{{ __('Dark mode') }}'"></span>
                                    <span class="ds-more-hint">{{ __('Appearance') }}</span>
                                </span>
                            </button>
                            <form method="POST" action="{{ route('logout') }}">
                                @csrf
                                <button type="submit" class="ds-more-row danger">
                                    <span class="ds-more-ic">@include('components.mobile-bottom-nav-icon', ['icon' => 'logout'])</span>
                                    <span class="ds-more-copy">
                                        <span class="ds-more-label">{{ __('Sign out') }}</span>
                                        <span class="ds-more-hint">{{ $user->email }}</span>
                                    </span>
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            </section>
        </div>
    </div>
@endauth
