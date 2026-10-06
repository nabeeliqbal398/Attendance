@php
    $user = auth()->user();
    $today = \Carbon\Carbon::now();
    $firstName = explode(' ', trim($user->name))[0] ?? $user->name;
    $initials = collect(explode(' ', trim($user->name)))->take(2)->map(fn($p) => mb_substr($p, 0, 1))->implode('');
    $startOfWeek = $today->copy()->startOfWeek(\Carbon\Carbon::MONDAY);
@endphp

<x-app-layout>
    {{-- Slim header --}}
    <div class="ds-slim-head">
        <div>
            <div class="hd-eyebrow">{{ strtoupper($today->format('D · j M Y')) }}</div>
            <h1 class="hd-title">
                {{ __('Assalam-u-alaikum,') }}
                <br>
                <span style="color: var(--ds-ink-2); font-style: italic;">{{ $firstName }}</span>
            </h1>
        </div>
        <div class="flex items-center gap-1" x-data>
            <button type="button" @click="$store.darkMode.toggle()" class="ds-icon-btn ds-press" aria-label="{{ __('Toggle theme') }}">
                {{-- Sun (shown in dark mode → tap to switch to light) --}}
                <svg x-show="$store.darkMode.on" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.7" stroke="currentColor" class="h-5 w-5">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 3v2.25m6.364.386-1.591 1.591M21 12h-2.25m-.386 6.364-1.591-1.591M12 18.75V21m-4.773-4.227-1.591 1.591M5.25 12H3m4.227-4.773L5.636 5.636M15.75 12a3.75 3.75 0 1 1-7.5 0 3.75 3.75 0 0 1 7.5 0Z" />
                </svg>
                {{-- Moon (shown in light mode → tap to switch to dark) --}}
                <svg x-show="!$store.darkMode.on" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.7" stroke="currentColor" class="h-5 w-5">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M21.752 15.002A9.72 9.72 0 0 1 18 15.75c-5.385 0-9.75-4.365-9.75-9.75 0-1.33.266-2.597.748-3.752A9.753 9.753 0 0 0 3 11.25C3 16.635 7.365 21 12.75 21a9.753 9.753 0 0 0 9.002-5.998Z" />
                </svg>
            </button>
            <a href="{{ route('profile.show') }}" wire:navigate.hover aria-label="{{ __('Profile') }}" class="ds-av ds-av-3 ds-press" style="width: 36px; height: 36px; font-size: 12px;">
                @if($user->profile_photo_url)
                    <img src="{{ $user->profile_photo_url }}" alt="{{ $user->name }}" class="h-full w-full rounded-full object-cover">
                @else
                    {{ $initials }}
                @endif
            </a>
        </div>
    </div>

    {{-- Punch card --}}
    @livewire('home-attendance-status')

    {{-- Week strip --}}
    <div class="ds-week-strip">
        @for($i = 0; $i < 7; $i++)
            @php
                $day = $startOfWeek->copy()->addDays($i);
                $isToday = $day->isSameDay($today);
            @endphp
            <a href="{{ route('my-schedule') }}" wire:navigate class="ds-week-chip {{ $isToday ? 'today' : '' }}">
                <div class="sc-d">{{ $day->format('D') }}</div>
                <div class="sc-n">{{ $day->format('j') }}</div>
            </a>
        @endfor
    </div>

    {{-- Today's schedule preview --}}
    <div class="ds-block">
        <div class="ds-sec-head">
            <span>{{ __("Today's schedule") }}</span>
            <a href="{{ route('my-schedule') }}" wire:navigate class="sec-link">{{ __('View all') }}</a>
        </div>
        @livewire('upcoming-events-widget')
    </div>

    {{-- This month summary --}}
    <div class="ds-block">
        <div class="ds-sec-head">
            <span>{{ __('This month') }} · {{ $today->format('F') }}</span>
            <a href="{{ route('attendance-history') }}" wire:navigate class="sec-link">{{ __('Details ›') }}</a>
        </div>
        @livewire('attendance-summary-widget')
    </div>

    {{-- Quick actions menu --}}
    <div class="ds-block">
        <div class="ds-sec-head">
            <span>{{ __('My menu') }}</span>
        </div>
        @livewire('quick-actions')
    </div>

    @push('scripts')
    <script>
        if (sessionStorage.getItem('force_reload_next')) {
            sessionStorage.removeItem('force_reload_next');
            window.location.reload();
        }
    </script>
    @endpush
</x-app-layout>
