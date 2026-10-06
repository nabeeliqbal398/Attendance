@props(['hasCheckedIn', 'hasCheckedOut', 'attendance', 'overtime' => null, 'quickCheckIn' => false])

<div class="bg-white dark:bg-gray-800 rounded-[1.5rem] p-5 shadow-lg border border-gray-100 dark:border-gray-700 relative overflow-hidden">

    {{-- Top Header --}}
    <div class="flex items-start justify-between mb-5">
        <div>
            <p class="text-[10px] font-semibold text-gray-400 dark:text-gray-500 uppercase tracking-widest mb-0.5">
                {{ __('Attendance') }}
            </p>
            <h2 class="text-sm font-bold text-gray-900 dark:text-white leading-tight">
                {{ \Carbon\Carbon::now()->translatedFormat('l, d F Y') }}
            </h2>
        </div>

        {{-- Live Badge --}}
        <div class="flex items-center gap-1.5 bg-primary-50 dark:bg-primary-900/30 px-2.5 py-1 rounded-full border border-primary-100 dark:border-primary-800">
            <span class="relative flex h-1.5 w-1.5">
              <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-primary-400 opacity-75"></span>
              <span class="relative inline-flex rounded-full h-1.5 w-1.5 bg-primary-500"></span>
            </span>
            <span class="text-[9px] font-semibold text-primary-600 dark:text-primary-400 uppercase tracking-wider">
                {{ __('Live') }}
            </span>
        </div>
    </div>

    {{-- Status Cards --}}
    <div class="grid grid-cols-2 gap-3 mb-5">
        {{-- Check In --}}
        <div class="bg-gray-50/80 dark:bg-gray-700/30 rounded-xl p-3 border border-gray-100 dark:border-gray-700/50">
            <div class="flex items-center gap-1.5 mb-1">
                <div class="w-1.5 h-1.5 rounded-full bg-primary-500"></div>
                <span class="text-[9px] font-semibold text-gray-400 dark:text-gray-500 uppercase tracking-wider">
                    {{ __('Check In') }}
                </span>
            </div>
            <div class="text-lg font-bold text-gray-900 dark:text-white font-mono tracking-tight">
                {{ $attendance?->time_in ? \Carbon\Carbon::parse($attendance->time_in)->format('H:i') : '--:--' }}
            </div>
        </div>

        {{-- Check Out --}}
        <div class="bg-gray-50/80 dark:bg-gray-700/30 rounded-xl p-3 border border-gray-100 dark:border-gray-700/50">
             <div class="flex items-center gap-1.5 mb-1">
                <div class="w-1.5 h-1.5 rounded-full bg-orange-500"></div>
                <span class="text-[9px] font-semibold text-gray-400 dark:text-gray-500 uppercase tracking-wider">
                    {{ __('Check Out') }}
                </span>
            </div>
            <div class="text-lg font-bold text-gray-900 dark:text-white font-mono tracking-tight">
                {{ $attendance?->time_out ? \Carbon\Carbon::parse($attendance->time_out)->format('H:i') : '--:--' }}
            </div>
        </div>
    </div>

    {{-- Actions with Quick GPS Check-In --}}
    <div x-data="quickAttendance()" x-cloak>

        {{-- Error Message --}}
        <template x-if="errorMsg">
            <div class="mb-3 p-2 bg-red-50 dark:bg-red-900/30 rounded-lg text-center">
                <p class="text-xs text-red-600 dark:text-red-400" x-text="errorMsg"></p>
            </div>
        </template>

        @if(!$hasCheckedIn)
            <p class="text-center text-[11px] font-medium text-gray-500 dark:text-gray-400 mb-3">
                {{ __('Ready to start your shift?') }}
            </p>
            <div class="grid grid-cols-2 gap-3">
                <button @click="doQuickCheckIn()" :disabled="isLoading"
                    class="flex flex-col items-center justify-center gap-1.5 p-3 rounded-xl bg-primary-600 hover:bg-primary-700 text-white shadow-lg shadow-primary-500/30 transition-all group disabled:opacity-50 disabled:cursor-wait">
                    <div class="p-1 bg-white/20 rounded-lg group-hover:bg-white/30 transition">
                        <template x-if="!isLoading || action !== 'checkin'">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 16l-4-4m0 0l4-4m-4 4h14m-5 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h7a3 3 0 013 3v1"></path></svg>
                        </template>
                        <template x-if="isLoading && action === 'checkin'">
                            <svg class="w-4 h-4 animate-spin" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path></svg>
                        </template>
                    </div>
                    <span class="text-xs font-semibold" x-text="isLoading && action === 'checkin' ? '{{ __("Getting GPS...") }}' : '{{ __("Check In") }}'"></span>
                </button>

                <button disabled class="flex flex-col items-center justify-center gap-1.5 p-3 rounded-xl bg-gray-50 dark:bg-gray-700 text-gray-400 border border-gray-100 dark:border-gray-600 cursor-not-allowed">
                     <div class="p-1 bg-gray-200 dark:bg-gray-600 rounded-lg">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h7a3 3 0 013 3v1"></path></svg>
                    </div>
                    <span class="text-xs font-semibold">{{ __('Check Out') }}</span>
                </button>
            </div>

        @elseif(!$hasCheckedOut)
            @php
                $shiftEndTime = ($attendance && $attendance->shift)
                    ? \Carbon\Carbon::parse($attendance->date)->format('Y-m-d') . ' ' . $attendance->shift->end_time
                    : null;
                $checkInTime = $attendance?->time_in ? \Carbon\Carbon::parse($attendance->time_in)->toIso8601String() : null;
            @endphp

            {{-- Live Timer --}}
            <div x-data="workedTimer('{{ $checkInTime }}')" class="mb-3">
                <div class="bg-primary-50 dark:bg-primary-900/20 rounded-xl p-2 text-center border border-primary-100 dark:border-primary-800/50">
                    <span class="text-lg font-bold font-mono text-primary-600 dark:text-primary-400" x-text="formatted"></span>
                    <p class="text-[9px] text-primary-500 dark:text-primary-400 uppercase tracking-widest mt-0.5">{{ __('Hours Worked') }}</p>
                </div>
            </div>

            <div class="grid grid-cols-2 gap-3">
                <button disabled class="flex flex-col items-center justify-center gap-1.5 p-3 rounded-xl bg-gray-50 dark:bg-gray-700 text-gray-400 border border-gray-100 dark:border-gray-600 cursor-not-allowed">
                    <div class="p-1 bg-gray-200 dark:bg-gray-600 rounded-lg">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 16l-4-4m0 0l4-4m-4 4h14m-5 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h7a3 3 0 013 3v1"></path></svg>
                    </div>
                    <span class="text-xs font-semibold">{{ __('Check In') }}</span>
                </button>

                <button @click="doQuickCheckOut()" :disabled="isLoading"
                    class="flex flex-col items-center justify-center gap-1.5 p-3 rounded-xl bg-white border-2 border-orange-500 text-orange-600 hover:bg-orange-50 shadow-lg shadow-orange-500/10 transition-all group disabled:opacity-50 disabled:cursor-wait">
                    <div class="p-1 bg-orange-100 rounded-lg group-hover:bg-orange-200 transition">
                        <template x-if="!isLoading || action !== 'checkout'">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h7a3 3 0 013 3v1"></path></svg>
                        </template>
                        <template x-if="isLoading && action === 'checkout'">
                            <svg class="w-4 h-4 animate-spin" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path></svg>
                        </template>
                    </div>
                    <span class="text-xs font-semibold" x-text="isLoading && action === 'checkout' ? '{{ __("Getting GPS...") }}' : '{{ __("Check Out") }}'"></span>
                </button>
            </div>
        @endif
    </div>
</div>

@pushOnce('scripts')
<script>
document.addEventListener('alpine:init', () => {
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
                if (result === true) {
                    // Success — Livewire will refresh the component
                } else {
                    this.errorMsg = result;
                }
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
                if (result === true) {
                    // Success — Livewire will refresh the component
                } else {
                    this.errorMsg = result;
                }
            } catch (e) {
                this.errorMsg = e.message || 'Failed to get location. Please enable GPS.';
            } finally {
                this.isLoading = false;
            }
        },

        async collectGpsSamples() {
            const samples = [];
            for (let i = 0; i < 3; i++) {
                const reading = await this.getSingleGpsReading();
                samples.push(reading);
                if (i < 2) await new Promise(r => setTimeout(r, 400));
            }
            const last = samples[samples.length - 1];
            const variance = this.calculateGpsVariance(samples);
            return { lat: last.lat, lng: last.lng, accuracy: last.accuracy, variance };
        },

        getSingleGpsReading() {
            return new Promise((resolve, reject) => {
                if (!navigator.geolocation) {
                    reject(new Error('Geolocation is not supported by your browser.'));
                    return;
                }
                navigator.geolocation.getCurrentPosition(
                    (pos) => resolve({ lat: pos.coords.latitude, lng: pos.coords.longitude, accuracy: pos.coords.accuracy }),
                    (err) => reject(new Error('Location permission denied. Please enable GPS.')),
                    { enableHighAccuracy: true, timeout: 15000, maximumAge: 3000 }
                );
            });
        },

        calculateGpsVariance(samples) {
            if (samples.length < 2) return 0;
            const lats = samples.map(s => s.lat);
            const lngs = samples.map(s => s.lng);
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
            this.timer = setInterval(() => {
                this.elapsed = Math.floor((Date.now() - start) / 1000);
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
