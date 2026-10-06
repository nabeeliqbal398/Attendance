<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ $appName ?? config('app.name', 'Laravel') }}</title>
    <link rel="icon" type="image/png" href="{{ asset('images/icons/favicon-circle.png') }}">

    <!-- PWA iOS & Splash -->
    <meta name="theme-color" content="#ffffff">
    <meta name="mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="default">
    <meta name="apple-mobile-web-app-title" content="Innovexify">
    <link rel="apple-touch-icon" href="{{ asset('images/icons/apple-touch-icon.png') }}">


    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=JetBrains+Mono:wght@400;500&family=Source+Serif+4:opsz,wght@8..60,400;8..60,500;8..60,600&display=swap" rel="stylesheet" />

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

    <!-- Scripts -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <!-- Styles -->
    @livewireStyles
</head>

<body class="font-sans antialiased">


    <div class="font-sans text-gray-900 antialiased dark:text-gray-100 pt-[env(safe-area-inset-top)] pb-[env(safe-area-inset-bottom)]">

        <div class="absolute right-4 top-4 flex gap-2">
            <x-theme-toggle x-data />
        </div>

        {{ $slot }}

    </div>

    @livewireScripts

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
                },
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
    </script>
</body>

</html>