<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ config('app.name', 'Attendance App') }}{{ isset($title) ? ' · ' . $title : '' }}</title>
    <link rel="icon" type="image/png" href="{{ asset('images/icons/favicon-circle.png') }}">

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=JetBrains+Mono:wght@400;500&family=Source+Serif+4:opsz,wght@8..60,400;8..60,500;8..60,600&display=swap" rel="stylesheet" />
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11" defer></script>
    <link href="https://cdn.jsdelivr.net/npm/tom-select@2.4.1/dist/css/tom-select.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/tom-select@2.4.1/dist/js/tom-select.complete.min.js" defer></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js" defer></script>

    <!-- PWA -->
    <link rel="manifest" href="{{ asset('manifest.json') }}">
    <meta name="theme-color" content="#faf9f7" media="(prefers-color-scheme: light)">
    <meta name="theme-color" content="#1a1816" media="(prefers-color-scheme: dark)">
    <meta name="mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="default">
    <meta name="apple-mobile-web-app-title" content="{{ config('app.name', 'Attendance App') }}">
    <link rel="apple-touch-icon" href="{{ asset('images/icons/apple-touch-icon.png') }}">


    <script>
        if ('serviceWorker' in navigator) {
            window.addEventListener('load', () => {
                navigator.serviceWorker.register('/sw.js');
            });
        }
    </script>

    <script>
        if (localStorage.getItem('isDark') === 'true' || (!('isDark' in localStorage) && window.matchMedia('(prefers-color-scheme: dark)').matches)) {
            document.documentElement.classList.add('dark');
        } else {
            document.documentElement.classList.remove('dark');
        }
    </script>

    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @livewireStyles
    @stack('styles')

    {{-- View Transitions API hint for browsers that support it (Livewire 3 leverages it for wire:navigate) --}}
    <meta name="view-transition" content="same-origin">

    {{-- Prefetch DNS for the fonts we already preconnect to (cheap, parallel) --}}
    <link rel="dns-prefetch" href="https://fonts.gstatic.com">
</head>

<body class="font-sans antialiased ds-with-sidebar"
      x-data
      x-init="document.body.dataset.sbCollapsed = (localStorage.getItem('ds-sb-collapsed') === '1') ? 'true' : 'false'">


    <a href="#main-content" class="ds-skip-link">{{ __('Skip to content') }}</a>
    <div class="ds-route-loader" aria-hidden="true"><span></span></div>
    <x-banner />

    {{-- Desktop sidebar (≥sm) replaces the old top nav --}}
    <x-app-sidebar />

    {{-- Quick Find command palette (⌘K / Ctrl+K) --}}
    <x-quick-find />

    <div class="min-h-screen bg-[var(--ds-bg)] dark:bg-gray-900 pt-[env(safe-area-inset-top)] sm:pt-0 pb-[calc(5rem+env(safe-area-inset-bottom))] sm:pb-0">

        <main id="main-content" tabindex="-1">
            {{ $slot }}
        </main>
    </div>

    @stack('modals')
    <x-feature-lock-modal />
    <x-mobile-bottom-nav />
    <script>
        window.isNativeApp = function() {
            return !!window.Capacitor && window.Capacitor.isNativePlatform();
        };

        document.addEventListener('DOMContentLoaded', () => {
            @if(session('show-feature-lock'))
            window.dispatchEvent(new CustomEvent('feature-lock', {
                detail: @json(session('show-feature-lock'))
            }));
            @endif
        });
    </script>

    @livewireScripts

    {{-- Global Notification --}}
    <div x-data="{ show: false, message: '' }"
        x-on:saved.window="show = true; message = $event.detail?.message || 'Saved successfully'; setTimeout(() => show = false, 2000)"
        class="fixed bottom-6 right-6 z-[9999]"
        style="display: none;"
        x-show="show"
        x-transition:enter="transform ease-out duration-300 transition"
        x-transition:enter-start="translate-y-2 opacity-0 sm:translate-y-0 sm:translate-x-2"
        x-transition:enter-end="translate-y-0 opacity-100 sm:translate-x-0"
        x-transition:leave="transition ease-in duration-200"
        x-transition:leave-start="opacity-100"
        x-transition:leave-end="opacity-0">
        <div class="flex items-center gap-3 rounded-lg bg-primary-600 px-4 py-3 text-white shadow-lg">
            <svg class="h-6 w-6 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
            </svg>
            <span class="font-medium" x-text="message"></span>
        </div>
    </div>

    @stack('scripts')


    <script>
        document.addEventListener('alpine:init', () => {
            Alpine.store("darkMode", {
                on: false,
                init() {
                    if (localStorage.getItem("isDark")) {
                        this.on = localStorage.getItem("isDark") === "true";
                    } else {
                        this.on = window.matchMedia("(prefers-color-scheme: dark)").matches;
                    }

                    if (this.on) {
                        document.documentElement.classList.add("dark");
                    } else {
                        document.documentElement.classList.remove("dark");
                    }
                },
                toggle() {
                    this.on = !this.on;
                    localStorage.setItem("isDark", this.on);
                    if (this.on) {
                        document.documentElement.classList.add("dark");
                    } else {
                        document.documentElement.classList.remove("dark");
                    }
                }
            });

            Alpine.data('tomSelectInput', (options, placeholder, wireModel, disabled = false) => ({
                tomSelectInstance: null,
                options: options,
                value: wireModel,
                disabled: disabled,

                init() {
                    if (this.tomSelectInstance) {
                        this.tomSelectInstance.sync();
                        return;
                    }

                    const config = {
                        create: false,
                        dropdownParent: 'body',
                        sortField: {
                            field: '$order'
                        },
                        valueField: 'id',
                        labelField: 'name',
                        searchField: 'name',
                        placeholder: placeholder,
                        onChange: (value) => {
                            this.value = value;
                        },
                        onDropdownOpen: () => {
                            if (this.tomSelectInstance) this.tomSelectInstance.positionDropdown();
                        }
                    };

                    if (this.options && this.options.length > 0) {
                        config.options = this.options;
                    }

                    this.tomSelectInstance = new TomSelect(this.$refs.select, config);

                    this.$watch('value', (newValue) => {
                        if (!this.tomSelectInstance) return;
                        const currentValue = this.tomSelectInstance.getValue();
                        if (newValue != currentValue) {
                            this.tomSelectInstance.setValue(newValue, true);
                        }
                    });

                    if (this.value) {
                        this.tomSelectInstance.setValue(this.value, true);
                    }

                    if (this.disabled) {
                        this.tomSelectInstance.lock();
                    }

                    this.$watch('disabled', (isDisabled) => {
                        if (!this.tomSelectInstance) return;
                        if (isDisabled) {
                            this.tomSelectInstance.lock();
                        } else {
                            this.tomSelectInstance.unlock();
                        }
                    });
                },

                destroy() {
                    if (this.tomSelectInstance) {
                        this.tomSelectInstance.destroy();
                        this.tomSelectInstance = null;
                    }
                }
            }));
        });

        document.addEventListener('DOMContentLoaded', function() {
            // Toast Configuration
            // Toast Configuration
            const Toast = Swal.mixin({
                toast: true,
                position: 'top-end',
                showConfirmButton: false,
                timer: 3000,
                timerProgressBar: true,
                background: 'transparent',
                customClass: {
                    popup: '!bg-white dark:!bg-gray-800 !text-gray-900 dark:!text-white !rounded-3xl !shadow-xl !border !border-gray-100 dark:!border-gray-700/50 !px-4 !py-3 !w-auto !max-w-[90vw] !mx-auto !mt-4',
                    title: '!text-sm !font-bold',
                    timerProgressBar: '!bg-primary-500 !h-1'
                },
                didOpen: (toast) => {
                    toast.addEventListener('mouseenter', Swal.stopTimer)
                    toast.addEventListener('mouseleave', Swal.resumeTimer)
                }
            });
            window.Toast = Toast;

            // Listen for Livewire Events
            if (typeof Livewire !== 'undefined') {
                Livewire.on('success', (data) => {
                    Toast.fire({
                        icon: 'success',
                        title: data.message || data
                    });
                });

                Livewire.on('error', (data) => {
                    Toast.fire({
                        icon: 'error',
                        title: data.message || data
                    });
                });

                Livewire.on('warning', (data) => {
                    Toast.fire({
                        icon: 'warning',
                        title: data.message || data
                    });
                });

                Livewire.on('info', (data) => {
                    Toast.fire({
                        icon: 'info',
                        title: data.message || data
                    });
                });
            }

            @if(session('success'))
            Toast.fire({
                icon: 'success',
                title: "{{ session('success') }}"
            });
            @endif

            @if(session('error'))
            Toast.fire({
                icon: 'error',
                title: "{{ session('error') }}"
            });
            @endif

            @if(session('warning'))
            Toast.fire({
                icon: 'warning',
                title: "{{ session('warning') }}"
            });
            @endif

            @if(session('info'))
            Toast.fire({
                icon: 'info',
                title: "{{ session('info') }}"
            });
            @endif

            @if(session('flash.banner'))
            Toast.fire({
                icon: 'success',
                title: "{{ session('flash.banner') }}"
            });
            @endif
        });
    </script>
</body>

</html>
