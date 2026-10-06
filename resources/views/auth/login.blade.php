<x-guest-layout>
    <div class="flex min-h-screen flex-col items-center justify-center bg-gray-50 px-4 py-12 dark:bg-gray-950 sm:px-6 lg:px-8">
        <div class="w-full max-w-md space-y-8">
            <div class="text-center">
                {{-- Logo and Title --}}
                {{-- Logo and Title --}}
                <div class="mx-auto flex justify-center">
                   <img src="{{ asset('images/icons/logo.jpeg') }}" class="h-16 w-16 rounded-full object-cover shadow-lg" alt="{{ config('app.name') }}">
                </div>
                <h2 class="mt-6 text-3xl font-bold tracking-tight text-gray-900 dark:text-white">
                    {{ __('Welcome Back!') }}
                </h2>
                <p class="mt-2 text-sm text-gray-600 dark:text-gray-400">
                    {{ __('Sign in to access your dashboard') }}
                </p>
            </div>

            <div class="mt-8 rounded-2xl bg-white px-6 py-8 shadow-xl dark:bg-gray-900 sm:px-10 border border-gray-100 dark:border-gray-800">
                <x-validation-errors class="mb-4" />

                @if (session('status'))
                    <div class="mb-4 rounded-lg bg-green-50 p-4 dark:bg-green-900/50">
                        <div class="flex">
                            <div class="flex-shrink-0">
                                <svg class="h-5 w-5 text-green-400" viewBox="0 0 20 20" fill="currentColor">
                                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                                </svg>
                            </div>
                            <div class="ml-3">
                                <p class="text-sm font-medium text-green-800 dark:text-green-200">
                                    {{ session('status') }}
                                </p>
                            </div>
                        </div>
                    </div>
                @endif

                <form method="POST" action="{{ route('login') }}" class="space-y-6">
                    @csrf

                    <div>
                        <label for="email" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                            {{ __('Email or Phone') }}
                        </label>
                        <div class="mt-1 relative rounded-md shadow-sm">
                            <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3">
                                <svg class="h-5 w-5 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 12a4 4 0 10-8 0 4 4 0 008 0zm0 0v1.5a2.5 2.5 0 005 0V12a9 9 0 10-9 9m4.5-1.206a8.959 8.959 0 01-4.5 1.207" />
                                </svg>
                            </div>
                            <input id="email" name="email" type="text" autocomplete="username" required autofocus
                                class="block w-full rounded-lg border-gray-300 pl-10 focus:border-primary-500 focus:ring-primary-500 dark:border-gray-700 dark:bg-gray-800 dark:text-white sm:text-sm py-2.5"
                                value="{{ old('email') }}" placeholder="{{ __('Enter your ID') }}">
                        </div>
                    </div>

                    <div x-data="{ show: false }">
                        <label for="password" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                            {{ __('Password') }}
                        </label>
                        <div class="mt-1 relative rounded-md shadow-sm">
                            <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3">
                                <svg class="h-5 w-5 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
                                </svg>
                            </div>
                            <input id="password" name="password" :type="show ? 'text' : 'password'" autocomplete="current-password" required
                                class="block w-full rounded-lg border-gray-300 pl-10 pr-10 focus:border-primary-500 focus:ring-primary-500 dark:border-gray-700 dark:bg-gray-800 dark:text-white sm:text-sm py-2.5"
                                placeholder="{{ __('••••••••') }}">
                            <button type="button" @click="show = !show" tabindex="-1"
                                class="absolute inset-y-0 right-0 flex items-center pr-3 text-gray-400 hover:text-gray-600 dark:hover:text-gray-200 focus:outline-none"
                                :aria-label="show ? '{{ __('Hide password') }}' : '{{ __('Show password') }}'">
                                <svg x-show="!show" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                </svg>
                                <svg x-show="show" x-cloak class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.542-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.542 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21" />
                                </svg>
                            </button>
                        </div>
                    </div>

                    {{-- Stay-signed-in is always on; users only log out via the Sign Out menu. --}}
                    <input type="hidden" name="remember" value="on">

                    @if (Route::has('password.request'))
                        <div class="flex justify-end text-sm">
                            <a href="{{ route('password.request') }}" class="font-medium text-primary-600 hover:text-primary-500 dark:text-primary-400 dark:hover:text-primary-300">
                                {{ __('Forgot password?') }}
                            </a>
                        </div>
                    @endif

                    <div>
                        <button type="submit"
                            class="group relative flex w-full justify-center rounded-lg border border-transparent bg-primary-600 py-2.5 px-4 text-sm font-medium text-white hover:bg-primary-700 focus:outline-none focus:ring-2 focus:ring-primary-500 focus:ring-offset-2 dark:focus:ring-offset-gray-900 transition-colors">
                            <span class="absolute inset-y-0 left-0 flex items-center pl-3">
                                <svg class="h-5 w-5 text-primary-500 group-hover:text-primary-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 16l-4-4m0 0l4-4m-4 4h14" />
                                </svg>
                            </span>
                            {{ __('Log in') }}
                        </button>
                    </div>
                </form>

                <div class="mt-6">
                    <div class="relative">
                        <div class="absolute inset-0 flex items-center">
                            <div class="w-full border-t border-gray-200 dark:border-gray-700"></div>
                        </div>
                        <div class="relative flex justify-center text-sm">
                            <span class="bg-white px-2 text-gray-500 dark:bg-gray-900 dark:text-gray-400">
                                {{ __('Or continue with') }}
                            </span>
                        </div>
                    </div>

                    <div class="mt-6 text-center">
                        <p class="text-sm text-gray-600 dark:text-gray-400">
                            {{ __("Don't have an account?") }}
                            <a href="{{ route('register') }}" class="font-medium text-primary-600 hover:text-primary-500 dark:text-primary-400">
                                {{ __('Register') }}
                            </a>
                        </p>
                    </div>
                </div>
            </div>
            
            {{-- Footer Text --}}
            <p class="text-center text-xs text-gray-500 dark:text-gray-500">
                &copy; {{ date('Y') }} {{ config('app.name') }}. All rights reserved.
            </p>
            <p class="text-center text-xs text-gray-400 dark:text-gray-600 mt-1">
                Developed by Pentox Studio
            </p>
        </div>
    </div>
</x-guest-layout>
