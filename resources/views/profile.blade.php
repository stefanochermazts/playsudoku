<x-site-layout>
    <div class="min-h-screen bg-gradient-to-br from-primary-50 via-neutral-50 to-secondary-50 dark:from-neutral-900 dark:via-neutral-950 dark:to-neutral-900">
        <!-- Header -->
        <div class="bg-white/80 dark:bg-neutral-900/80 backdrop-blur-sm border-b border-neutral-200 dark:border-neutral-700">
            <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
                <div class="flex items-center space-x-4">
                    <div class="w-16 h-16 bg-gradient-to-br from-primary-500 via-secondary-500 to-accent-500 rounded-2xl flex items-center justify-center shadow-lg">
                        <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                        </svg>
                    </div>
                    <div>
                        <h1 class="text-3xl font-bold text-neutral-900 dark:text-white">{{ __('app.nav.profile') }}</h1>
                        <p class="text-neutral-600 dark:text-neutral-300 mt-1">{{ __('Manage your account settings and preferences') }}</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Content -->
        <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8 py-8" x-data="{ tab: (new URLSearchParams(window.location.search).get('tab')) || 'profile' }">
            <div class="grid grid-cols-1 lg:grid-cols-12 gap-8">
                <!-- Sidebar -->
                <nav class="lg:col-span-3 bg-white/70 dark:bg-neutral-800/70 backdrop-blur-sm rounded-3xl p-4 border border-neutral-200/50 dark:border-neutral-700/50 shadow-xl" aria-label="{{ __('Impostazioni account') }}">
                    <ul class="space-y-1" role="list">
                        <li>
                            <button type="button" class="w-full text-left px-4 py-3 rounded-xl font-medium text-neutral-800 dark:text-neutral-100 hover:bg-neutral-100/70 dark:hover:bg-neutral-700/50 focus:outline-none focus:ring-2 focus:ring-primary-500" :class="{ 'bg-neutral-100 dark:bg-neutral-700/60 aria-[current=page]:font-semibold': tab === 'profile' }" @click="tab='profile'" :aria-current="tab==='profile' ? 'page' : null">
                                {{ __('Profile') }}
                            </button>
                        </li>
                        <li>
                            <button type="button" class="w-full text-left px-4 py-3 rounded-xl font-medium text-neutral-800 dark:text-neutral-100 hover:bg-neutral-100/70 dark:hover:bg-neutral-700/50 focus:outline-none focus:ring-2 focus:ring-primary-500" :class="{ 'bg-neutral-100 dark:bg-neutral-700/60 aria-[current=page]:font-semibold': tab === 'privacy' }" @click="tab='privacy'" :aria-current="tab==='privacy' ? 'page' : null">
                                {{ __('Privacy') }}
                            </button>
                        </li>
                        <li>
                            <button type="button" class="w-full text-left px-4 py-3 rounded-xl font-medium text-neutral-800 dark:text-neutral-100 hover:bg-neutral-100/70 dark:hover:bg-neutral-700/50 focus:outline-none focus:ring-2 focus:ring-primary-500" :class="{ 'bg-neutral-100 dark:bg-neutral-700/60 aria-[current=page]:font-semibold': tab === 'security' }" @click="tab='security'" :aria-current="tab==='security' ? 'page' : null">
                                {{ __('Security') }}
                            </button>
                        </li>
                        <li>
                            <button type="button" class="w-full text-left px-4 py-3 rounded-xl font-medium text-neutral-800 dark:text-neutral-100 hover:bg-neutral-100/70 dark:hover:bg-neutral-700/50 focus:outline-none focus:ring-2 focus:ring-primary-500" :class="{ 'bg-neutral-100 dark:bg-neutral-700/60 aria-[current=page]:font-semibold': tab === 'account' }" @click="tab='account'" :aria-current="tab==='account' ? 'page' : null">
                                {{ __('Account') }}
                            </button>
                        </li>
                    </ul>
                </nav>

                <!-- Content area -->
                <div class="lg:col-span-9 space-y-8">
                    <!-- Profile Information -->
                    <section x-show="tab==='profile'" x-cloak class="bg-white/70 dark:bg-neutral-800/70 backdrop-blur-sm rounded-3xl p-8 border border-neutral-200/50 dark:border-neutral-700/50 shadow-xl" aria-labelledby="profile-heading">
                        <div class="flex items-center space-x-3 mb-6">
                            <div class="w-8 h-8 bg-gradient-to-r from-primary-500 to-primary-600 rounded-lg flex items-center justify-center">
                                <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5.121 17.804A13.937 13.937 0 0112 16c2.5 0 4.847.655 6.879 1.804M15 10a3 3 0 11-6 0 3 3 0 016 0zm6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                            </div>
                            <h2 id="profile-heading" class="text-xl font-semibold text-neutral-900 dark:text-white">{{ __('Profile Information') }}</h2>
                        </div>
                        <div class="max-w-xl">
                            <livewire:profile.update-profile-information-form />
                        </div>
                    </section>

                    <!-- Privacy Settings -->
                    <section x-show="tab==='privacy'" x-cloak class="bg-white/70 dark:bg-neutral-800/70 backdrop-blur-sm rounded-3xl p-8 border border-neutral-200/50 dark:border-neutral-700/50 shadow-xl" aria-labelledby="privacy-heading">
                        <div class="flex items-center space-x-3 mb-6">
                            <div class="w-8 h-8 bg-gradient-to-r from-blue-500 to-blue-600 rounded-lg flex items-center justify-center">
                                <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/></svg>
                            </div>
                            <h2 id="privacy-heading" class="text-xl font-semibold text-neutral-900 dark:text-white">{{ __('app.nav.privacy') ?? 'Privacy' }}</h2>
                        </div>
                        <div class="space-y-6 max-w-xl">
                            @php($visibilityOptions = ['public' => __('app.privacy.public'), 'friends' => __('app.privacy.friends_only'), 'private' => __('app.privacy.private')])
                            @include('privacy._form', ['user' => auth()->user(), 'visibilityOptions' => $visibilityOptions])
                        </div>
                    </section>

                    <!-- Security -->
                    <section x-show="tab==='security'" x-cloak class="bg-white/70 dark:bg-neutral-800/70 backdrop-blur-sm rounded-3xl p-8 border border-neutral-200/50 dark:border-neutral-700/50 shadow-xl" aria-labelledby="security-heading">
                        <div class="flex items-center space-x-3 mb-6">
                            <div class="w-8 h-8 bg-gradient-to-r from-secondary-500 to-secondary-600 rounded-lg flex items-center justify-center">
                                <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/></svg>
                            </div>
                            <h2 id="security-heading" class="text-xl font-semibold text-neutral-900 dark:text-white">{{ __('Update Password') }}</h2>
                        </div>
                        <div class="max-w-xl">
                            <livewire:profile.update-password-form />
                        </div>
                    </section>

                    <!-- Account -->
                    <section x-show="tab==='account'" x-cloak class="bg-white/70 dark:bg-neutral-800/70 backdrop-blur-sm rounded-3xl p-8 border border-danger-200/50 dark:border-danger-700/50 shadow-xl" aria-labelledby="account-heading">
                        <div class="flex items-center space-x-3 mb-6">
                            <div class="w-8 h-8 bg-gradient-to-r from-danger-500 to-danger-600 rounded-lg flex items-center justify-center">
                                <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                            </div>
                            <h2 id="account-heading" class="text-xl font-semibold text-danger-900 dark:text-danger-100">{{ __('Delete Account') }}</h2>
                        </div>
                        <div class="max-w-xl">
                            <livewire:profile.delete-user-form />
                        </div>
                    </section>
                </div>
            </div>
        </div>
    </div>
</x-site-layout>
