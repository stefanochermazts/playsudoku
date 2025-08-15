<div class="min-h-screen bg-gradient-to-br from-blue-50 to-indigo-100 dark:from-gray-900 dark:to-blue-900 py-8">
    <div class="container mx-auto px-4">
        <div class="max-w-2xl mx-auto">
            
            @if($isSubmitted)
                {{-- Success Message --}}
                <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-xl p-8 text-center">
                    <div class="w-20 h-20 bg-green-100 dark:bg-green-900 rounded-full flex items-center justify-center mx-auto mb-6">
                        <svg class="w-10 h-10 text-green-600 dark:text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                        </svg>
                    </div>
                    
                    <h2 class="text-2xl font-bold text-gray-900 dark:text-white mb-4">
                        {{ __('app.contact.success_title') }}
                    </h2>
                    
                    <p class="text-gray-600 dark:text-gray-300 mb-8">
                        {{ __('app.contact.success_message') }}
                    </p>
                    
                    <div class="flex flex-col sm:flex-row gap-4 justify-center">
                        <button wire:click="resetForm" 
                                class="px-6 py-3 bg-blue-600 text-white rounded-lg hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 font-medium transition-colors">
                            {{ __('app.contact.send_another') }}
                        </button>
                        
                        <a href="{{ route('localized.sudoku.training', ['locale' => app()->getLocale()]) }}" 
                           class="px-6 py-3 bg-gray-600 text-white rounded-lg hover:bg-gray-700 focus:outline-none focus:ring-2 focus:ring-gray-500 font-medium transition-colors text-center">
                            {{ __('app.contact.back_to_home') }}
                        </a>
                    </div>
                </div>
            @else
                {{-- Contact Form --}}
                <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-xl p-8">
                    <div class="text-center mb-8">
                        <h1 class="text-3xl font-bold text-gray-900 dark:text-white mb-4">
                            {{ __('app.contact.title') }}
                        </h1>
                        <p class="text-lg text-gray-600 dark:text-gray-300">
                            {{ __('app.contact.subtitle') }}
                        </p>
                    </div>
                    
                    @if (session('error'))
                        <div class="bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800 rounded-lg p-4 mb-6">
                            <div class="flex">
                                <svg class="w-5 h-5 text-red-400 mr-3 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                </svg>
                                <p class="text-red-700 dark:text-red-300 font-medium">{{ session('error') }}</p>
                            </div>
                        </div>
                    @endif
                    
                    <form wire:submit="submit" class="space-y-6">
                        {{-- Name Field --}}
                        <div>
                            <label for="name" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                {{ __('app.contact.name') }} *
                            </label>
                            <input type="text" 
                                   id="name"
                                   wire:model="name"
                                   placeholder="{{ __('app.contact.name_placeholder') }}"
                                   class="w-full px-4 py-3 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:text-white transition-colors"
                                   required>
                            @error('name') 
                                <p class="mt-2 text-sm text-red-600 dark:text-red-400">{{ $message }}</p> 
                            @enderror
                        </div>
                        
                        {{-- Email Field --}}
                        <div>
                            <label for="email" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                {{ __('app.contact.email') }} *
                            </label>
                            <input type="email" 
                                   id="email"
                                   wire:model="email"
                                   placeholder="{{ __('app.contact.email_placeholder') }}"
                                   class="w-full px-4 py-3 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:text-white transition-colors"
                                   required>
                            @error('email') 
                                <p class="mt-2 text-sm text-red-600 dark:text-red-400">{{ $message }}</p> 
                            @enderror
                        </div>
                        
                        {{-- Subject Field --}}
                        <div>
                            <label for="subject" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                {{ __('app.contact.subject') }} *
                            </label>
                            <input type="text" 
                                   id="subject"
                                   wire:model="subject"
                                   placeholder="{{ __('app.contact.subject_placeholder') }}"
                                   class="w-full px-4 py-3 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:text-white transition-colors"
                                   required>
                            @error('subject') 
                                <p class="mt-2 text-sm text-red-600 dark:text-red-400">{{ $message }}</p> 
                            @enderror
                        </div>
                        
                        {{-- Message Field --}}
                        <div>
                            <label for="message" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                {{ __('app.contact.message') }} * 
                                <span class="text-xs text-gray-500 dark:text-gray-400 font-normal">
                                    ({{ app()->getLocale() === 'it' ? 'minimo 10 caratteri' : 'minimum 10 characters' }})
                                </span>
                            </label>
                            <textarea id="message"
                                      wire:model="message"
                                      rows="6"
                                      placeholder="{{ __('app.contact.message_placeholder') }}"
                                      class="w-full px-4 py-3 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:text-white transition-colors resize-vertical"
                                      required
                                      minlength="10"></textarea>
                            @error('message') 
                                <p class="mt-2 text-sm text-red-600 dark:text-red-400">{{ $message }}</p> 
                            @enderror
                            <div class="mt-1 text-xs text-gray-500 dark:text-gray-400">
                                <span wire:ignore>{{ strlen($message) }}</span>/2000 {{ app()->getLocale() === 'it' ? 'caratteri' : 'characters' }}
                            </div>
                        </div>
                        
                        {{-- Privacy Policy Checkbox --}}
                        <div class="flex items-start space-x-3">
                            <input type="checkbox" 
                                   wire:model="privacy_accepted" 
                                   id="privacy_accepted_contact" 
                                   required
                                   class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 dark:border-gray-600 rounded mt-1">
                            <label for="privacy_accepted_contact" class="text-sm text-gray-700 dark:text-gray-300 leading-relaxed">
                                {{ __('app.privacy.accept_privacy') }} 
                                <a href="{{ route('localized.privacy', ['locale' => app()->getLocale()]) }}" 
                                   target="_blank"
                                   class="text-blue-600 dark:text-blue-400 hover:underline font-medium">
                                    {{ __('app.privacy.title') }}
                                </a>
                                e i 
                                <a href="{{ route('localized.terms', ['locale' => app()->getLocale()]) }}" 
                                   target="_blank"
                                   class="text-blue-600 dark:text-blue-400 hover:underline font-medium">
                                    {{ __('app.terms.title') }}
                                </a>
                            </label>
                        </div>
                        @error('privacy_accepted') 
                            <p class="mt-2 text-sm text-red-600 dark:text-red-400">{{ $message }}</p> 
                        @enderror
                        
                        {{-- Submit Button --}}
                        <div class="pt-4">
                            <button type="submit" 
                                    wire:loading.attr="disabled"
                                    class="w-full px-6 py-4 bg-blue-600 text-white rounded-lg hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 font-medium text-lg transition-colors disabled:opacity-50 disabled:cursor-not-allowed">
                                <span wire:loading.remove>{{ __('app.contact.send_button') }}</span>
                                <span wire:loading class="flex items-center justify-center">
                                    <svg class="animate-spin -ml-1 mr-3 h-5 w-5 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                    </svg>
                                    {{ __('app.contact.sending') }}
                                </span>
                            </button>
                        </div>
                    </form>
                </div>
            @endif
        </div>
    </div>
</div>