<div class="py-10">
    <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
        <div class="mb-7 flex flex-col gap-2 sm:flex-row sm:items-end sm:justify-between">
            <div>
                <h2 class="text-2xl font-bold tracking-tight text-gray-900 dark:text-gray-100">
                    {{ __('Geofence Locations') }}
                </h2>
                <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                    {{ __('Manage the GPS areas where employees are allowed to check in and check out.') }}
                </p>
            </div>
            <div class="rounded-full bg-primary-50 px-3 py-1 text-xs font-semibold text-primary-700 dark:bg-primary-900/30 dark:text-primary-300">
                {{ $locations->where('is_active', true)->count() }} {{ __('active') }}
            </div>
        </div>

        <div class="grid gap-6 lg:grid-cols-[minmax(0,420px)_1fr]">
            <form
                wire:submit.prevent="save"
                x-data="{
                    locating: false,
                    gpsError: null,
                    useCurrentLocation() {
                        this.gpsError = null;
                        if (!navigator.geolocation) {
                            this.gpsError = {{ \Illuminate\Support\Js::from(__('Geolocation is not supported by this browser.')) }};
                            return;
                        }
                        this.locating = true;
                        navigator.geolocation.getCurrentPosition(
                            (pos) => {
                                $wire.set('latitude', pos.coords.latitude.toFixed(7));
                                $wire.set('longitude', pos.coords.longitude.toFixed(7));
                                this.locating = false;
                            },
                            () => {
                                this.gpsError = {{ \Illuminate\Support\Js::from(__('Location permission denied. Enable GPS and try again.')) }};
                                this.locating = false;
                            },
                            { enableHighAccuracy: true, timeout: 15000, maximumAge: 3000 }
                        );
                    }
                }"
                class="rounded-xl border border-gray-200 bg-white shadow-sm dark:border-gray-700 dark:bg-gray-800"
            >
                <div class="border-b border-gray-100 px-5 py-4 dark:border-gray-700">
                    <h3 class="text-base font-semibold text-gray-900 dark:text-white">
                        {{ $editingId ? __('Edit Location') : __('Add Location') }}
                    </h3>
                    <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">
                        {{ __('Radius is measured in meters from the saved GPS point.') }}
                    </p>
                </div>

                <div class="space-y-4 p-5">
                    <div>
                        <x-label for="name" value="{{ __('Location name') }}" />
                        <x-input id="name" wire:model.defer="name" class="mt-1 block w-full" type="text" placeholder="{{ __('Head Office') }}" />
                        <x-input-error for="name" class="mt-2" />
                    </div>

                    <div>
                        <x-label for="address" value="{{ __('Address / note') }}" />
                        <x-input id="address" wire:model.defer="address" class="mt-1 block w-full" type="text" placeholder="{{ __('Optional') }}" />
                        <x-input-error for="address" class="mt-2" />
                    </div>

                    <div class="grid grid-cols-1 gap-3 sm:grid-cols-2">
                        <div>
                            <x-label for="latitude" value="{{ __('Latitude') }}" />
                            <x-input id="latitude" wire:model.defer="latitude" class="mt-1 block w-full" type="text" placeholder="31.482914" />
                            <x-input-error for="latitude" class="mt-2" />
                        </div>
                        <div>
                            <x-label for="longitude" value="{{ __('Longitude') }}" />
                            <x-input id="longitude" wire:model.defer="longitude" class="mt-1 block w-full" type="text" placeholder="74.396186" />
                            <x-input-error for="longitude" class="mt-2" />
                        </div>
                    </div>

                    <button type="button" class="inline-flex items-center gap-2 text-sm font-semibold text-primary-600 hover:text-primary-700 dark:text-primary-400" @click="useCurrentLocation()" :disabled="locating">
                        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 2.25v3m0 12v4.5m9.75-9.75h-4.5m-12 0H2.25m15.364-6.364-2.121 2.121M8.507 15.493l-2.121 2.121m11.228 0-2.121-2.121M8.507 8.507 6.386 6.386" />
                        </svg>
                        <span x-text="locating ? {{ \Illuminate\Support\Js::from(__('Getting current GPS...')) }} : {{ \Illuminate\Support\Js::from(__('Use this device location')) }}"></span>
                    </button>

                    <template x-if="gpsError">
                        <p class="rounded-lg bg-red-50 px-3 py-2 text-xs text-red-600 dark:bg-red-900/30 dark:text-red-300" x-text="gpsError"></p>
                    </template>

                    <div class="grid grid-cols-1 gap-3 sm:grid-cols-2">
                        <div>
                            <x-label for="radius" value="{{ __('Allowed radius') }}" />
                            <div class="mt-1 flex rounded-lg shadow-sm">
                                <x-input id="radius" wire:model.defer="radius" class="block w-full rounded-r-none" type="number" min="1" max="10000" />
                                <span class="inline-flex items-center rounded-r-lg border border-l-0 border-gray-300 bg-gray-50 px-3 text-sm text-gray-500 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-400">m</span>
                            </div>
                            <x-input-error for="radius" class="mt-2" />
                        </div>
                        <label class="flex items-center gap-3 self-end rounded-lg border border-gray-200 px-3 py-2.5 text-sm text-gray-700 dark:border-gray-700 dark:text-gray-300">
                            <input type="checkbox" wire:model.defer="is_active" class="rounded border-gray-300 text-primary-600 shadow-sm focus:ring-primary-500 dark:border-gray-700 dark:bg-gray-900">
                            <span>{{ __('Active for check-in') }}</span>
                        </label>
                    </div>

                    <div class="flex items-center justify-end gap-3 pt-2">
                        @if($editingId)
                            <x-secondary-button type="button" wire:click="resetForm">
                                {{ __('Cancel') }}
                            </x-secondary-button>
                        @endif
                        <x-button>
                            {{ $editingId ? __('Update location') : __('Save location') }}
                        </x-button>
                    </div>
                </div>
            </form>

            <div class="rounded-xl border border-gray-200 bg-white shadow-sm dark:border-gray-700 dark:bg-gray-800">
                <div class="border-b border-gray-100 px-5 py-4 dark:border-gray-700">
                    <h3 class="text-base font-semibold text-gray-900 dark:text-white">{{ __('Allowed GPS Areas') }}</h3>
                    <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">{{ __('Employees can clock in from any active location in this list.') }}</p>
                </div>

                <div class="divide-y divide-gray-100 dark:divide-gray-700">
                    @forelse($locations as $location)
                        <div wire:key="geofence-location-{{ $location->id }}" class="p-5">
                            <div class="flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between">
                                <div class="min-w-0">
                                    <div class="flex flex-wrap items-center gap-2">
                                        <h4 class="truncate text-sm font-semibold text-gray-900 dark:text-white">{{ $location->name }}</h4>
                                        <span class="rounded-full px-2 py-0.5 text-[11px] font-semibold {{ $location->is_active ? 'bg-green-50 text-green-700 dark:bg-green-900/30 dark:text-green-300' : 'bg-gray-100 text-gray-600 dark:bg-gray-700 dark:text-gray-300' }}">
                                            {{ $location->is_active ? __('Active') : __('Disabled') }}
                                        </span>
                                    </div>
                                    @if($location->address)
                                        <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">{{ $location->address }}</p>
                                    @endif
                                    <div class="mt-2 flex flex-wrap items-center gap-x-4 gap-y-1 text-xs text-gray-500 dark:text-gray-400">
                                        <a href="https://www.google.com/maps/search/?api=1&query={{ $location->latitude }},{{ $location->longitude }}" target="_blank" class="font-mono hover:text-primary-600 hover:underline">
                                            {{ number_format($location->latitude, 6) }}, {{ number_format($location->longitude, 6) }}
                                        </a>
                                        <span>{{ __('Radius') }}: {{ $location->radius }}m</span>
                                    </div>
                                </div>

                                <div class="flex shrink-0 items-center gap-2">
                                    <x-secondary-button type="button" wire:click="toggleActive({{ $location->id }})">
                                        {{ $location->is_active ? __('Disable') : __('Enable') }}
                                    </x-secondary-button>
                                    <x-secondary-button type="button" wire:click="edit({{ $location->id }})">
                                        {{ __('Edit') }}
                                    </x-secondary-button>
                                    <button type="button" wire:click="confirmDeletion({{ $location->id }})" class="inline-flex items-center rounded-lg border border-red-200 bg-red-50 px-3 py-2 text-sm font-semibold text-red-700 transition hover:bg-red-100 dark:border-red-900/50 dark:bg-red-900/20 dark:text-red-300">
                                        {{ __('Delete') }}
                                    </button>
                                </div>
                            </div>
                        </div>
                    @empty
                        <div class="p-8 text-center">
                            <div class="mx-auto mb-3 flex h-12 w-12 items-center justify-center rounded-xl bg-gray-50 text-gray-400 dark:bg-gray-700">
                                <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke-width="1.7" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 21s7.5-4.917 7.5-11.25a7.5 7.5 0 1 0-15 0C4.5 16.083 12 21 12 21Z" />
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 12.75a3 3 0 1 0 0-6 3 3 0 0 0 0 6Z" />
                                </svg>
                            </div>
                            <h4 class="text-sm font-semibold text-gray-900 dark:text-white">{{ __('No geofence locations yet') }}</h4>
                            <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">{{ __('Add at least one active location before employees use GPS check-in.') }}</p>
                        </div>
                    @endforelse
                </div>
            </div>
        </div>
    </div>

    <x-confirmation-modal wire:model="confirmingDeletion">
        <x-slot name="title">
            {{ __('Delete Geofence Location') }}
        </x-slot>

        <x-slot name="content">
            {{ __('Are you sure you want to delete') }} <b>{{ $deleteName }}</b>?
        </x-slot>

        <x-slot name="footer">
            <x-secondary-button wire:click="$toggle('confirmingDeletion')" wire:loading.attr="disabled">
                {{ __('Cancel') }}
            </x-secondary-button>

            <x-danger-button class="ml-2" wire:click="delete" wire:loading.attr="disabled">
                {{ __('Confirm') }}
            </x-danger-button>
        </x-slot>
    </x-confirmation-modal>
</div>
