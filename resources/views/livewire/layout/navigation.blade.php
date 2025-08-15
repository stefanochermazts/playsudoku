<?php

use App\Livewire\Actions\Logout;
use Livewire\Volt\Component;

new class extends Component
{
    /**
     * Log the current user out of the application.
     */
    public function logout(Logout $logout): void
    {
        $logout();

        // Redirect to localized home if available
        $locale = app()->getLocale();
        $redirectUrl = url('/' . $locale);
        
        $this->redirect($redirectUrl, navigate: true);
    }
}; ?>

<nav x-data="{ open: false }" class="bg-white dark:bg-gray-800 border-b border-gray-100 dark:border-gray-700">
    @if(session()->has('impersonator_id'))
        <div class="bg-warning-100 dark:bg-yellow-900/40 text-warning-900 dark:text-yellow-200">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-2 flex items-center justify-between">
                <div class="text-sm font-medium">🔄 Impersonazione attiva</div>
                <form action="{{ route('impersonation.stop') }}" method="POST">
                    @csrf
                    <button type="submit" class="px-3 py-1 bg-warning-600 hover:bg-warning-700 text-white rounded-md text-sm focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-warning-500" aria-label="{{ __('app.aria.end_impersonation') }}">
                        Torna al mio account
                    </button>
                </form>
            </div>
        </div>
    @endif
    <!-- Primary Navigation Menu -->
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex justify-between h-16">
            <div class="flex">
                <!-- Logo -->
                <div class="shrink-0 flex items-center">
                    <a href="{{ route('dashboard') }}" wire:navigate>
                        <x-application-logo class="block h-9 w-auto fill-current text-gray-800 dark:text-gray-200" />
                    </a>
                </div>

                <!-- Navigation Links -->
                <div class="hidden space-x-4 xl:space-x-6 sm:-my-px sm:ms-10 sm:flex overflow-x-auto">
                    @auth
                    <x-nav-link :href="route('dashboard')" :active="request()->routeIs('dashboard')" wire:navigate>
                        {{ __('app.nav.dashboard') }}
                    </x-nav-link>
                    <x-nav-link :href="route('localized.challenges.index', ['locale' => app()->getLocale()])" :active="request()->routeIs('localized.challenges.*')" wire:navigate>
                        {{ __('app.nav.challenges') }}
                    </x-nav-link>
                    <x-nav-link :href="route('localized.sudoku.training', ['locale' => app()->getLocale()])" :active="request()->routeIs('localized.sudoku.*')" wire:navigate>
                        {{ __('app.nav.training') }}
                    </x-nav-link>
                    <x-nav-link :href="route('localized.sudoku.analyzer', ['locale' => app()->getLocale()])" :active="request()->routeIs('localized.sudoku.analyzer')" wire:navigate>
                        {{ __('app.nav.analyzer') }}
                    </x-nav-link>
                    <x-nav-link :href="route('localized.friends.index', ['locale' => app()->getLocale()])" :active="request()->routeIs('localized.friends.*')" wire:navigate>
                        {{ __('app.nav.friends') }}
                    </x-nav-link>
                    <x-nav-link :href="route('localized.daily-board.index', ['locale' => app()->getLocale()])" :active="request()->routeIs('localized.daily-board.*')" wire:navigate>
                        {{ __('app.daily_board') }}
                    </x-nav-link>
                    <x-nav-link :href="route('localized.weekly-board.index', ['locale' => app()->getLocale()])" :active="request()->routeIs('localized.weekly-board.*')" wire:navigate>
                        {{ __('app.weekly_board') }}
                    </x-nav-link>
                    @if(auth()->user() && auth()->user()->isAdmin())
                        <x-nav-link :href="route('admin.dashboard')" :active="request()->routeIs('admin.*')" wire:navigate>
                            👑 Admin
                        </x-nav-link>
                    @endif
                    @endauth
                    
                    @guest
                    <x-nav-link :href="route('sudoku.demo')" :active="request()->routeIs('sudoku.*')" wire:navigate>
                        🎮 Demo Sudoku
                    </x-nav-link>
                    @endguest
                </div>
            </div>

            <!-- Settings Dropdown -->
            <div class="hidden sm:flex sm:items-center sm:ms-6">
                <!-- Language switcher -->
                <x-dropdown align="right" width="48">
                    <x-slot name="trigger">
                        <button class="inline-flex items-center px-3 py-2 border border-transparent text-sm leading-4 font-medium rounded-md text-gray-500 dark:text-gray-400 bg-white dark:bg-gray-800 hover:text-gray-700 dark:hover:text-gray-300 focus:outline-none transition ease-in-out duration-150" aria-label="{{ __('app.nav.language') }}">
                            <div class="me-1">{{ strtoupper(app()->getLocale()) }}</div>

                            <div class="ms-1">
                                <svg class="fill-current h-4 w-4" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd" />
                                </svg>
                            </div>
                        </button>
                    </x-slot>

                    <x-slot name="content">
                        <x-dropdown-link :href="url('/en')">
                            EN
                        </x-dropdown-link>
                        <x-dropdown-link :href="url('/it')">
                            IT
                        </x-dropdown-link>
                    </x-slot>
                </x-dropdown>

                @auth
                <x-dropdown align="right" width="48">
                    <x-slot name="trigger">
                        <button class="inline-flex items-center px-3 py-2 border border-transparent text-sm leading-4 font-medium rounded-md text-gray-500 dark:text-gray-400 bg-white dark:bg-gray-800 hover:text-gray-700 dark:hover:text-gray-300 focus:outline-none transition ease-in-out duration-150">
                            <div x-data="{{ json_encode(['name' => auth()->user()->name]) }}" x-text="name" x-on:profile-updated.window="name = $event.detail.name"></div>

                            <div class="ms-1">
                                <svg class="fill-current h-4 w-4" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd" />
                                </svg>
                            </div>
                        </button>
                    </x-slot>

                    <x-slot name="content">
                        <x-dropdown-link :href="route('profile')" wire:navigate>
                            {{ __('Profile') }}
                        </x-dropdown-link>

                        <!-- Authentication -->
                        <button wire:click="logout" class="w-full text-start">
                            <x-dropdown-link>
                                {{ __('Log Out') }}
                            </x-dropdown-link>
                        </button>
                    </x-slot>
                </x-dropdown>
                @endauth

                @guest
                <div class="flex items-center space-x-4">
                    <a href="{{ route('login') }}" class="text-gray-500 dark:text-gray-400 hover:text-gray-700 dark:hover:text-gray-300 px-3 py-2 text-sm font-medium">
                        Login
                    </a>
                    <a href="{{ route('register') }}" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-md text-sm font-medium">
                        Register
                    </a>
                </div>
                @endguest
            </div>

            <!-- Hamburger -->
            <div class="-me-2 flex items-center sm:hidden">
                <button @click="open = ! open" class="inline-flex items-center justify-center p-2 rounded-md text-gray-400 dark:text-gray-500 hover:text-gray-500 dark:hover:text-gray-400 hover:bg-gray-100 dark:hover:bg-gray-900 focus:outline-none focus:bg-gray-100 dark:focus:bg-gray-900 focus:text-gray-500 dark:focus:text-gray-400 transition duration-150 ease-in-out">
                    <svg class="h-6 w-6" stroke="currentColor" fill="none" viewBox="0 0 24 24">
                        <path :class="{'hidden': open, 'inline-flex': ! open }" class="inline-flex" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                        <path :class="{'hidden': ! open, 'inline-flex': open }" class="hidden" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>
        </div>
    </div>

    <!-- Responsive Navigation Menu -->
    <div :class="{'block': open, 'hidden': ! open}" class="hidden sm:hidden">
        @auth
        <div class="pt-2 pb-3 space-y-1">
            <x-responsive-nav-link :href="route('dashboard')" :active="request()->routeIs('dashboard')" wire:navigate>
                {{ __('app.nav.dashboard') }}
            </x-responsive-nav-link>
            <x-responsive-nav-link :href="route('localized.challenges.index', ['locale' => app()->getLocale()])" :active="request()->routeIs('localized.challenges.*')" wire:navigate>
                {{ __('app.nav.challenges') }}
            </x-responsive-nav-link>
            <x-responsive-nav-link :href="route('localized.sudoku.training', ['locale' => app()->getLocale()])" :active="request()->routeIs('localized.sudoku.*')" wire:navigate>
                {{ __('app.nav.training') }}
            </x-responsive-nav-link>
            <x-responsive-nav-link :href="route('localized.sudoku.analyzer', ['locale' => app()->getLocale()])" :active="request()->routeIs('localized.sudoku.analyzer')" wire:navigate>
                {{ __('app.nav.analyzer') }}
            </x-responsive-nav-link>
            <x-responsive-nav-link :href="route('localized.friends.index', ['locale' => app()->getLocale()])" :active="request()->routeIs('localized.friends.*')" wire:navigate>
                {{ __('app.nav.friends') }}
            </x-responsive-nav-link>
            <x-responsive-nav-link :href="route('localized.daily-board.index', ['locale' => app()->getLocale()])" :active="request()->routeIs('localized.daily-board.*')" wire:navigate>
                {{ __('app.daily_board') }}
            </x-responsive-nav-link>
            <x-responsive-nav-link :href="route('localized.weekly-board.index', ['locale' => app()->getLocale()])" :active="request()->routeIs('localized.weekly-board.*')" wire:navigate>
                {{ __('app.weekly_board') }}
            </x-responsive-nav-link>
        </div>

        <!-- Responsive Settings Options -->
        <div class="pt-4 pb-1 border-t border-gray-200 dark:border-gray-600">
            <div class="px-4">
                <div class="font-medium text-base text-gray-800 dark:text-gray-200" x-data="{{ json_encode(['name' => auth()->user()->name]) }}" x-text="name" x-on:profile-updated.window="name = $event.detail.name"></div>
                <div class="font-medium text-sm text-gray-500">{{ auth()->user()->email }}</div>
            </div>

            <div class="mt-3 space-y-1">
                <x-responsive-nav-link :href="route('profile')" wire:navigate>
                    {{ __('Profile') }}
                </x-responsive-nav-link>

                <!-- Authentication -->
                <button wire:click="logout" class="w-full text-start">
                    <x-responsive-nav-link>
                        {{ __('Log Out') }}
                    </x-responsive-nav-link>
                </button>
            </div>
        </div>
        @endauth

        @guest
        <div class="pt-2 pb-3 space-y-1">
            <x-responsive-nav-link :href="route('login')">
                Login
            </x-responsive-nav-link>
            <x-responsive-nav-link :href="route('register')">
                Register
            </x-responsive-nav-link>
        </div>
        @endguest
    </div>
</nav>
