<?php

use App\Livewire\Forms\LoginForm;
use Illuminate\Support\Facades\Session;
use Livewire\Attributes\Layout;
use Livewire\Volt\Component;

new #[Layout('layouts.site')] class extends Component
{
    public LoginForm $form;

    /**
     * Handle an incoming authentication request.
     */
    public function login(): void
    {
        $this->validate();

        $this->form->authenticate();

        Session::regenerate();

        // Redirect to localized dashboard if we're in a localized route
        $locale = app()->getLocale();
        $defaultRoute = route('dashboard', absolute: false);
        
        // Check if we're in a localized context
        if (request()->route() && request()->route()->hasParameter('locale')) {
            $defaultRoute = route('localized.dashboard', ['locale' => $locale], false);
        }

        $this->redirectIntended(default: $defaultRoute, navigate: true);
    }
}; ?>

<div class="min-h-screen flex items-center justify-center py-12 px-4 sm:px-6 lg:px-8">
    <div class="max-w-md w-full">
        <!-- Header -->
        <div class="text-center mb-8">
            <div class="flex justify-center mb-6">
                <div class="w-16 h-16 bg-gradient-to-br from-primary-500 via-secondary-500 to-accent-500 rounded-2xl flex items-center justify-center shadow-lg">
                    <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M9 7h6m0 10v-3m-3 3h.01M9 17h.01M9 14h.01M12 14h.01M15 11h.01M12 11h.01M9 11h.01M7 21h10a2 2 0 002-2V5a2 2 0 00-2-2H7a2 2 0 00-2 2v14a2 2 0 002 2z"></path>
                    </svg>
                </div>
            </div>
            <h2 class="text-3xl font-bold text-neutral-900 dark:text-white">{{ __('auth.Welcome back') }}</h2>
            <p class="mt-2 text-neutral-600 dark:text-neutral-300">{{ __('auth.Sign in to your account') }}</p>
        </div>

        <!-- Card -->
        <div class="bg-white/70 dark:bg-neutral-800/70 backdrop-blur-sm rounded-3xl p-8 border border-neutral-200/50 dark:border-neutral-700/50 shadow-xl">
            <!-- Session Status -->
            <x-auth-session-status class="mb-6" :status="session('status')" />

            <form wire:submit="login" class="space-y-6">
                <!-- Email Address -->
                <div>
                    <x-input-label for="email" :value="__('auth.Email')" class="text-neutral-700 dark:text-neutral-300 font-medium" />
                    <x-text-input wire:model="form.email" id="email" 
                                  class="mt-2 block w-full px-4 py-3 bg-white dark:bg-neutral-800 border border-neutral-300 dark:border-neutral-600 rounded-xl focus:ring-2 focus:ring-primary-500 focus:border-primary-500 dark:focus:ring-primary-400 dark:focus:border-primary-400 transition-colors text-neutral-900 dark:text-white placeholder-neutral-500 dark:placeholder-neutral-400" 
                                  type="email" name="email" required autofocus autocomplete="username" 
                                  placeholder="{{ __('auth.Enter your email') }}" />
                    <x-input-error :messages="$errors->get('form.email')" class="mt-2" />
                </div>

                <!-- Password -->
                <div>
                    <x-input-label for="password" :value="__('auth.Password')" class="text-neutral-700 dark:text-neutral-300 font-medium" />
                    <x-text-input wire:model="form.password" id="password" 
                                  class="mt-2 block w-full px-4 py-3 bg-white dark:bg-neutral-800 border border-neutral-300 dark:border-neutral-600 rounded-xl focus:ring-2 focus:ring-primary-500 focus:border-primary-500 dark:focus:ring-primary-400 dark:focus:border-primary-400 transition-colors text-neutral-900 dark:text-white placeholder-neutral-500 dark:placeholder-neutral-400"
                                  type="password" name="password" required autocomplete="current-password" 
                                  placeholder="{{ __('auth.Enter your password') }}" />
                    <x-input-error :messages="$errors->get('form.password')" class="mt-2" />
                </div>

                <!-- Remember Me & Forgot Password -->
                <div class="flex items-center justify-between">
                    <label for="remember" class="inline-flex items-center">
                        <input wire:model="form.remember" id="remember" type="checkbox" 
                               class="w-4 h-4 text-primary-600 bg-white dark:bg-neutral-800 border-neutral-300 dark:border-neutral-600 rounded focus:ring-primary-500 dark:focus:ring-primary-400 focus:ring-2" 
                               name="remember">
                        <span class="ml-2 text-sm text-neutral-600 dark:text-neutral-300">{{ __('auth.Remember me') }}</span>
                    </label>

                    @if (Route::has('localized.password.request'))
                        <a class="text-sm text-primary-600 dark:text-primary-400 hover:text-primary-700 dark:hover:text-primary-300 font-medium transition-colors" 
                           href="{{ route('localized.password.request', ['locale' => app()->getLocale()]) }}" wire:navigate>
                            {{ __('auth.Forgot your password?') }}
                        </a>
                    @endif
                </div>

                <!-- Submit Button -->
                <button type="submit" 
                        class="w-full flex justify-center py-3 px-4 bg-gradient-to-r from-primary-600 to-secondary-600 text-white font-semibold rounded-xl hover:from-primary-700 hover:to-secondary-700 focus:outline-none focus:ring-2 focus:ring-primary-500 focus:ring-offset-2 dark:focus:ring-offset-neutral-900 transition-all transform hover:scale-[1.02] disabled:opacity-50 disabled:cursor-not-allowed">
                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 16l-4-4m0 0l4-4m-4 4h14m-5 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h7a3 3 0 013 3v1"></path>
                    </svg>
                    {{ __('auth.Sign in') }}
                </button>
            </form>

            <!-- Register Link -->
            <div class="mt-6 text-center">
                <p class="text-sm text-neutral-600 dark:text-neutral-300">
                    {{ __('auth.Don\'t have an account?') }}
                    <a href="{{ route('localized.register', ['locale' => app()->getLocale()]) }}" wire:navigate 
                       class="font-medium text-primary-600 dark:text-primary-400 hover:text-primary-700 dark:hover:text-primary-300 transition-colors">
                        {{ __('auth.Sign up') }}
                    </a>
                </p>
            </div>
        </div>
    </div>
</div>
