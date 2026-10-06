@props(['hasCheckedIn', 'hasCheckedOut', 'attendance', 'overtime' => null])

@php
    $checkInTime  = $attendance?->time_in  ? \Carbon\Carbon::parse($attendance->time_in)->format('H:i')  : null;
    $checkOutTime = $attendance?->time_out ? \Carbon\Carbon::parse($attendance->time_out)->format('H:i') : null;
    $checkInIso   = $attendance?->time_in  ? \Carbon\Carbon::parse($attendance->time_in)->toIso8601String() : null;
@endphp

<div class="ds-punch-card" x-data="quickAttendance()" x-cloak>

    {{-- Head: ATTENDANCE eyebrow + Today's shift + LIVE pill --}}
    <div class="ds-punch-head">
        <div>
            <div class="ds-punch-eyebrow">{{ __('ATTENDANCE') }}</div>
            <div class="ds-punch-date">{{ __("Today's shift") }}</div>
        </div>
        <span class="ds-punch-live">{{ __('LIVE') }}</span>
    </div>

    {{-- Check In / Check Out time cells --}}
    <div class="ds-punch-row">
        <div class="ds-punch-cell">
            <div class="ds-punch-lbl">{{ __('CHECK IN') }}</div>
            <div class="ds-punch-time {{ $checkInTime ? '' : 'empty' }}">
                {{ $checkInTime ?? '--:--' }}
            </div>
        </div>
        <div class="ds-punch-cell">
            <div class="ds-punch-lbl out">{{ __('CHECK OUT') }}</div>
            <div class="ds-punch-time {{ $checkOutTime ? '' : 'empty' }}">
                {{ $checkOutTime ?? '--:--' }}
            </div>
        </div>
    </div>

    {{-- Hours worked (live timer when checked-in but not out, total when done) --}}
    @if($hasCheckedIn && !$hasCheckedOut)
        <div class="ds-worked-row" x-data="workedTimer('{{ $checkInIso }}')">
            <div class="ds-worked-time" x-text="formatted"></div>
            <div class="ds-worked-lbl">{{ __('HOURS WORKED') }}</div>
        </div>
    @elseif($hasCheckedIn && $hasCheckedOut && $attendance?->time_in && $attendance?->time_out)
        @php
            $elapsed = \Carbon\Carbon::parse($attendance->time_in)->diffInSeconds(\Carbon\Carbon::parse($attendance->time_out));
            $h = floor($elapsed / 3600);
            $m = floor(($elapsed % 3600) / 60);
            $s = $elapsed % 60;
            $totalWorked = sprintf('%02d:%02d:%02d', $h, $m, $s);
        @endphp
        <div class="ds-worked-row">
            <div class="ds-worked-time">{{ $totalWorked }}</div>
            <div class="ds-worked-lbl">{{ __('HOURS WORKED') }}</div>
        </div>
    @else
        <div class="ds-worked-row">
            <div class="ds-worked-time">00:00:00</div>
            <div class="ds-worked-lbl">{{ __('READY TO START') }}</div>
        </div>
    @endif

    {{-- GPS error message --}}
    <template x-if="errorMsg">
        <div class="mb-3 mt-1 rounded-lg bg-red-50 p-2 text-center text-xs text-red-600 dark:bg-red-900/30 dark:text-red-400" x-text="errorMsg"></div>
    </template>

    {{-- Action buttons --}}
    <div class="ds-punch-actions">
        @if(!$hasCheckedIn)
            {{-- Idle: only check-in active --}}
            <button @click="doQuickCheckIn()" :disabled="isLoading" class="ds-punch-btn active-in">
                <span class="ic-w" x-show="!isLoading || action !== 'checkin'">
                    <svg width="20" height="20" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M11 16l-4-4m0 0l4-4m-4 4h14m-5 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h7a3 3 0 013 3v1"/></svg>
                </span>
                <span class="ic-w" x-show="isLoading && action === 'checkin'">
                    <svg width="20" height="20" fill="none" viewBox="0 0 24 24" class="animate-spin"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="3"/><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"/></svg>
                </span>
                <span x-text="isLoading && action === 'checkin' ? '{{ __('Locating...') }}' : '{{ __('Check in') }}'"></span>
            </button>
            <button disabled class="ds-punch-btn" style="opacity:0.5; cursor: not-allowed;">
                <span class="ic-w">
                    <svg width="18" height="18" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h7a3 3 0 013 3v1"/></svg>
                </span>
                {{ __('Check out') }}
            </button>
        @elseif(!$hasCheckedOut)
            {{-- Checked in: show "Checked in" pill + active check-out --}}
            <button disabled class="ds-punch-btn active-in">
                <span class="ic-w">
                    <svg width="20" height="20" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
                </span>
                {{ __('Checked in') }}
            </button>
            <button @click="doQuickCheckOut()" :disabled="isLoading" class="ds-punch-btn active-out">
                <span class="ic-w" x-show="!isLoading || action !== 'checkout'">
                    <svg width="18" height="18" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h7a3 3 0 013 3v1"/></svg>
                </span>
                <span class="ic-w" x-show="isLoading && action === 'checkout'">
                    <svg width="18" height="18" fill="none" viewBox="0 0 24 24" class="animate-spin"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="3"/><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"/></svg>
                </span>
                <span x-text="isLoading && action === 'checkout' ? '{{ __('Locating...') }}' : '{{ __('Check out') }}'"></span>
            </button>
        @else
            {{-- Done for the day --}}
            <button disabled class="ds-punch-btn active-in">
                <span class="ic-w">
                    <svg width="20" height="20" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
                </span>
                {{ __('Checked in') }}
            </button>
            <button disabled class="ds-punch-btn active-in">
                <span class="ic-w">
                    <svg width="18" height="18" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
                </span>
                {{ __('Checked out') }}
            </button>
        @endif
    </div>
</div>

@pushOnce('scripts')
<script>
document.addEventListener('alpine:init', () => {
    if (typeof Alpine === 'undefined' || Alpine._hasQuickAttendance) return;
    Alpine._hasQuickAttendance = true;

    Alpine.data('quickAttendance', () => ({
        isLoading: false,
        action: null,
        errorMsg: null,

        async doQuickCheckIn() {
            this.action = 'checkin';
            this.errorMsg = null;
            this.isLoading = true;
            try {
                const gps = await this.collectGpsSamples();
                const result = await @this.quickCheckIn(gps.lat, gps.lng, gps.accuracy, gps.variance);
                if (result !== true) this.errorMsg = result;
            } catch (e) {
                this.errorMsg = e.message || 'Failed to get location. Please enable GPS.';
            } finally {
                this.isLoading = false;
            }
        },
        async doQuickCheckOut() {
            this.action = 'checkout';
            this.errorMsg = null;
            this.isLoading = true;
            try {
                const gps = await this.collectGpsSamples();
                const result = await @this.quickCheckOut(gps.lat, gps.lng, gps.accuracy, gps.variance);
                if (result !== true) this.errorMsg = result;
            } catch (e) {
                this.errorMsg = e.message || 'Failed to get location. Please enable GPS.';
            } finally {
                this.isLoading = false;
            }
        },
        async collectGpsSamples() {
            const samples = [];
            for (let i = 0; i < 3; i++) {
                samples.push(await this.getSingleGpsReading());
                if (i < 2) await new Promise(r => setTimeout(r, 400));
            }
            const last = samples[samples.length - 1];
            return { lat: last.lat, lng: last.lng, accuracy: last.accuracy, variance: this.calculateGpsVariance(samples) };
        },
        getSingleGpsReading() {
            return new Promise((resolve, reject) => {
                if (!navigator.geolocation) return reject(new Error('Geolocation is not supported.'));
                navigator.geolocation.getCurrentPosition(
                    (pos) => resolve({ lat: pos.coords.latitude, lng: pos.coords.longitude, accuracy: pos.coords.accuracy }),
                    (err) => reject(new Error('Location permission denied. Please enable GPS.')),
                    { enableHighAccuracy: true, timeout: 15000, maximumAge: 3000 }
                );
            });
        },
        calculateGpsVariance(samples) {
            if (samples.length < 2) return 0;
            const lats = samples.map(s => s.lat), lngs = samples.map(s => s.lng);
            const avgLat = lats.reduce((a, b) => a + b, 0) / lats.length;
            const avgLng = lngs.reduce((a, b) => a + b, 0) / lngs.length;
            const latVar = lats.reduce((sum, l) => sum + Math.pow(l - avgLat, 2), 0) / lats.length;
            const lngVar = lngs.reduce((sum, l) => sum + Math.pow(l - avgLng, 2), 0) / lngs.length;
            return Math.sqrt(latVar + lngVar);
        }
    }));

    Alpine.data('workedTimer', (checkInIso) => ({
        elapsed: 0,
        timer: null,
        init() {
            if (!checkInIso) return;
            const start = new Date(checkInIso).getTime();
            this.elapsed = Math.max(0, Math.floor((Date.now() - start) / 1000));
            this.timer = setInterval(() => {
                this.elapsed = Math.max(0, Math.floor((Date.now() - start) / 1000));
            }, 1000);
        },
        destroy() { if (this.timer) clearInterval(this.timer); },
        get formatted() {
            const h = Math.floor(this.elapsed / 3600);
            const m = Math.floor((this.elapsed % 3600) / 60);
            const s = this.elapsed % 60;
            return String(h).padStart(2, '0') + ':' + String(m).padStart(2, '0') + ':' + String(s).padStart(2, '0');
        }
    }));
});
</script>
@endpushOnce
