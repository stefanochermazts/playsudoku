<?php

use App\Models\User;
use Illuminate\Auth\Events\Registered;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules;
use Livewire\Attributes\Layout;
use Livewire\Volt\Component;

new #[Layout('layouts.site')] class extends Component
{
    public string $name = '';
    public string $email = '';
    public string $password = '';
    public string $password_confirmation = '';
    public bool $privacy_accepted = false;

    /**
     * Handle an incoming registration request.
     */
    public function register(): void
    {
        $validated = $this->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'lowercase', 'email', 'max:255', 'unique:'.User::class],
            'password' => ['required', 'string', 'confirmed', Rules\Password::defaults()],
            'privacy_accepted' => ['required', 'accepted'],
        ], [
            'privacy_accepted.required' => __('app.privacy.must_accept'),
            'privacy_accepted.accepted' => __('app.privacy.must_accept'),
        ]);

        $validated['password'] = Hash::make($validated['password']);

        // Remove privacy_accepted from user data (it's not a User field)
        $userData = collect($validated)->except('privacy_accepted')->toArray();
        
        event(new Registered($user = User::create($userData)));

        Auth::login($user);

        // Redirect to localized dashboard if we're in a localized route
        $locale = app()->getLocale();
        $redirectRoute = route('dashboard', absolute: false);
        
        // Check if we're in a localized context
        if (request()->route() && request()->route()->hasParameter('locale')) {
            $redirectRoute = route('localized.dashboard', ['locale' => $locale], false);
        }

        $this->redirect($redirectRoute, navigate: true);
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
            <h2 class="text-3xl font-bold text-neutral-900 dark:text-white">{{ __('auth.Create account') }}</h2>
            <p class="mt-2 text-neutral-600 dark:text-neutral-300">{{ __('auth.Join the Sudoku community') }}</p>
        </div>

        <!-- Card -->
        <div class="bg-white/70 dark:bg-neutral-800/70 backdrop-blur-sm rounded-3xl p-8 border border-neutral-200/50 dark:border-neutral-700/50 shadow-xl">
            <form wire:submit="register" class="space-y-6">
                <!-- Name -->
                <div>
                    <x-input-label for="name" :value="__('auth.Name')" class="text-neutral-700 dark:text-neutral-300 font-medium" />
                    <x-text-input wire:model="name" id="name" 
                                  class="mt-2 block w-full px-4 py-3 bg-white dark:bg-neutral-800 border border-neutral-300 dark:border-neutral-600 rounded-xl focus:ring-2 focus:ring-primary-500 focus:border-primary-500 dark:focus:ring-primary-400 dark:focus:border-primary-400 transition-colors text-neutral-900 dark:text-white placeholder-neutral-500 dark:placeholder-neutral-400" 
                                  type="text" name="name" required autofocus autocomplete="name" 
                                  placeholder="{{ __('auth.Enter your name') }}" />
                    <x-input-error :messages="$errors->get('name')" class="mt-2" />
                </div>

                <!-- Email Address -->
                <div>
                    <x-input-label for="email" :value="__('auth.Email')" class="text-neutral-700 dark:text-neutral-300 font-medium" />
                    <x-text-input wire:model="email" id="email" 
                                  class="mt-2 block w-full px-4 py-3 bg-white dark:bg-neutral-800 border border-neutral-300 dark:border-neutral-600 rounded-xl focus:ring-2 focus:ring-primary-500 focus:border-primary-500 dark:focus:ring-primary-400 dark:focus:border-primary-400 transition-colors text-neutral-900 dark:text-white placeholder-neutral-500 dark:placeholder-neutral-400" 
                                  type="email" name="email" required autocomplete="username" 
                                  placeholder="{{ __('auth.Enter your email') }}" />
                    <x-input-error :messages="$errors->get('email')" class="mt-2" />
                </div>

                <!-- Password -->
                <div>
                    <x-input-label for="password" :value="__('auth.Password')" class="text-neutral-700 dark:text-neutral-300 font-medium" />
                    <x-text-input wire:model="password" id="password" 
                                  class="mt-2 block w-full px-4 py-3 bg-white dark:bg-neutral-800 border border-neutral-300 dark:border-neutral-600 rounded-xl focus:ring-2 focus:ring-primary-500 focus:border-primary-500 dark:focus:ring-primary-400 dark:focus:border-primary-400 transition-colors text-neutral-900 dark:text-white placeholder-neutral-500 dark:placeholder-neutral-400"
                                  type="password" name="password" required autocomplete="new-password" 
                                  placeholder="{{ __('auth.Enter your password') }}" />
                    <x-input-error :messages="$errors->get('password')" class="mt-2" />
                </div>

                <!-- Confirm Password -->
                <div>
                    <x-input-label for="password_confirmation" :value="__('auth.Confirm Password')" class="text-neutral-700 dark:text-neutral-300 font-medium" />
                    <x-text-input wire:model="password_confirmation" id="password_confirmation" 
                                  class="mt-2 block w-full px-4 py-3 bg-white dark:bg-neutral-800 border border-neutral-300 dark:border-neutral-600 rounded-xl focus:ring-2 focus:ring-primary-500 focus:border-primary-500 dark:focus:ring-primary-400 dark:focus:border-primary-400 transition-colors text-neutral-900 dark:text-white placeholder-neutral-500 dark:placeholder-neutral-400"
                                  type="password" name="password_confirmation" required autocomplete="new-password" 
                                  placeholder="{{ __('auth.Confirm your password') }}" />
                    <x-input-error :messages="$errors->get('password_confirmation')" class="mt-2" />
                </div>

                <!-- Privacy Policy Checkbox -->
                <div class="flex items-start space-x-3">
                    <input type="checkbox" 
                           wire:model="privacy_accepted" 
                           id="privacy_accepted" 
                           required
                           class="h-4 w-4 text-primary-600 focus:ring-primary-500 border-neutral-300 dark:border-neutral-600 rounded mt-1">
                    <label for="privacy_accepted" class="text-sm text-neutral-700 dark:text-neutral-300 leading-relaxed">
                        {{ __('app.privacy.accept_privacy') }} 
                        <a href="{{ route('localized.privacy', ['locale' => app()->getLocale()]) }}" 
                           target="_blank"
                           class="text-primary-600 dark:text-primary-400 hover:underline font-medium">
                            {{ __('app.privacy.title') }}
                        </a>
                        e i 
                        <a href="{{ route('localized.terms', ['locale' => app()->getLocale()]) }}" 
                           target="_blank"
                           class="text-primary-600 dark:text-primary-400 hover:underline font-medium">
                            {{ __('app.terms.title') }}
                        </a>
                    </label>
                </div>
                @error('privacy_accepted') 
                    <div class="text-sm text-red-600 dark:text-red-400">{{ $message }}</div> 
                @enderror

                <!-- Submit Button -->
                <button type="submit" 
                        class="w-full flex justify-center py-3 px-4 bg-gradient-to-r from-primary-600 to-secondary-600 text-white font-semibold rounded-xl hover:from-primary-700 hover:to-secondary-700 focus:outline-none focus:ring-2 focus:ring-primary-500 focus:ring-offset-2 dark:focus:ring-offset-neutral-900 transition-all transform hover:scale-[1.02] disabled:opacity-50 disabled:cursor-not-allowed">
                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z"></path>
                    </svg>
                    {{ __('auth.Create account') }}
                </button>
            </form>

            <!-- Login Link -->
            <div class="mt-6 text-center">
                <p class="text-sm text-neutral-600 dark:text-neutral-300">
                    {{ __('auth.Already have an account?') }}
                    <a href="{{ route('localized.login', ['locale' => app()->getLocale()]) }}" wire:navigate 
                       class="font-medium text-primary-600 dark:text-primary-400 hover:text-primary-700 dark:hover:text-primary-300 transition-colors">
                        {{ __('auth.Sign in') }}
                    </a>
                </p>
            </div>
        </div>
    </div>
</div>
