@props([
    'url' => null,
    'title' => null,
    'description' => null,
    'image' => null,
    'via' => 'PlaySudoku',
    'hashtags' => 'sudoku,puzzle,game',
    'size' => 'md', // sm, md, lg
    'style' => 'default', // default, minimal, floating
    'platforms' => ['facebook', 'twitter', 'linkedin', 'whatsapp', 'telegram', 'copy']
])

@php
    $shareUrl = $url ?? url()->current();
    $shareTitle = $title ?? (__('app.app_name') . ' - ' . __('app.meta.description'));
    $shareDescription = $description ?? __('app.meta.description');
    $shareImage = $image ?? asset('images/og-default.jpg'); // Sarà creata dopo
    
    // Size classes
    $sizeClasses = [
        'sm' => 'w-8 h-8 text-sm',
        'md' => 'w-10 h-10 text-base',
        'lg' => 'w-12 h-12 text-lg'
    ];
    
    $buttonSize = $sizeClasses[$size] ?? $sizeClasses['md'];
    
    // Style classes
    $styleClasses = match($style) {
        'minimal' => 'bg-gray-100 dark:bg-gray-700 text-gray-600 dark:text-gray-300 hover:bg-gray-200 dark:hover:bg-gray-600',
        'floating' => 'bg-white shadow-lg border border-gray-200 dark:border-gray-700 text-gray-600 dark:text-gray-300 hover:shadow-xl',
        default => 'transition-all duration-200 hover:scale-105'
    };

    // Share URLs
    $facebookUrl = 'https://www.facebook.com/sharer/sharer.php?' . http_build_query([
        'u' => $shareUrl,
        'quote' => $shareTitle
    ]);
    
    $twitterUrl = 'https://twitter.com/intent/tweet?' . http_build_query([
        'url' => $shareUrl,
        'text' => $shareTitle,
        'via' => $via,
        'hashtags' => $hashtags
    ]);
    
    $linkedinUrl = 'https://www.linkedin.com/sharing/share-offsite/?' . http_build_query([
        'url' => $shareUrl
    ]);
    
    $whatsappUrl = 'https://wa.me/?' . http_build_query([
        'text' => $shareTitle . ' - ' . $shareUrl
    ]);
    
    $telegramUrl = 'https://t.me/share/url?' . http_build_query([
        'url' => $shareUrl,
        'text' => $shareTitle
    ]);
@endphp

<div {{ $attributes->merge(['class' => 'social-share flex items-center gap-2']) }}>
    
    @if($style !== 'floating')
        <span class="text-sm text-gray-600 dark:text-gray-400 mr-2">{{ __('app.social.share') }}:</span>
    @endif
    
    <div class="flex items-center gap-2">
        
        @if(in_array('facebook', $platforms))
            <a href="{{ $facebookUrl }}" 
               target="_blank" 
               rel="noopener"
               onclick="window.open(this.href, 'facebook-share', 'width=580,height=296'); return false;"
               class="inline-flex items-center justify-center {{ $buttonSize }} {{ $styleClasses }} rounded-lg bg-blue-600 text-white hover:bg-blue-700"
               title="{{ __('app.social.share_facebook') }}"
               data-share-platform="facebook"
               data-share-url="{{ $shareUrl }}">
                <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24">
                    <path d="M24 12.073c0-6.627-5.373-12-12-12s-12 5.373-12 12c0 5.99 4.388 10.954 10.125 11.854v-8.385H7.078v-3.47h3.047V9.43c0-3.007 1.792-4.669 4.533-4.669 1.312 0 2.686.235 2.686.235v2.953H15.83c-1.491 0-1.956.925-1.956 1.874v2.25h3.328l-.532 3.47h-2.796v8.385C19.612 23.027 24 18.062 24 12.073z"/>
                </svg>
            </a>
        @endif
        
        @if(in_array('twitter', $platforms))
            <a href="{{ $twitterUrl }}" 
               target="_blank" 
               rel="noopener"
               onclick="window.open(this.href, 'twitter-share', 'width=550,height=450'); return false;"
               class="inline-flex items-center justify-center {{ $buttonSize }} {{ $styleClasses }} rounded-lg bg-sky-500 text-white hover:bg-sky-600"
               title="{{ __('app.social.share_twitter') }}"
               data-share-platform="twitter"
               data-share-url="{{ $shareUrl }}">
                <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24">
                    <path d="M23.953 4.57a10 10 0 01-2.825.775 4.958 4.958 0 002.163-2.723c-.951.555-2.005.959-3.127 1.184a4.92 4.92 0 00-8.384 4.482C7.69 8.095 4.067 6.13 1.64 3.162a4.822 4.822 0 00-.666 2.475c0 1.71.87 3.213 2.188 4.096a4.904 4.904 0 01-2.228-.616v.06a4.923 4.923 0 003.946 4.827 4.996 4.996 0 01-2.212.085 4.936 4.936 0 004.604 3.417 9.867 9.867 0 01-6.102 2.105c-.39 0-.779-.023-1.17-.067a13.995 13.995 0 007.557 2.209c9.053 0 13.998-7.496 13.998-13.985 0-.21 0-.42-.015-.63A9.935 9.935 0 0024 4.59z"/>
                </svg>
            </a>
        @endif
        
        @if(in_array('linkedin', $platforms))
            <a href="{{ $linkedinUrl }}" 
               target="_blank" 
               rel="noopener"
               onclick="window.open(this.href, 'linkedin-share', 'width=520,height=570'); return false;"
               class="inline-flex items-center justify-center {{ $buttonSize }} {{ $styleClasses }} rounded-lg bg-blue-700 text-white hover:bg-blue-800"
               title="{{ __('app.social.share_linkedin') }}"
               data-share-platform="linkedin"
               data-share-url="{{ $shareUrl }}">
                <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24">
                    <path d="M20.447 20.452h-3.554v-5.569c0-1.328-.027-3.037-1.852-3.037-1.853 0-2.136 1.445-2.136 2.939v5.667H9.351V9h3.414v1.561h.046c.477-.9 1.637-1.85 3.37-1.85 3.601 0 4.267 2.37 4.267 5.455v6.286zM5.337 7.433c-1.144 0-2.063-.926-2.063-2.065 0-1.138.92-2.063 2.063-2.063 1.14 0 2.064.925 2.064 2.063 0 1.139-.925 2.065-2.064 2.065zm1.782 13.019H3.555V9h3.564v11.452zM22.225 0H1.771C.792 0 0 .774 0 1.729v20.542C0 23.227.792 24 1.771 24h20.451C23.2 24 24 23.227 24 22.271V1.729C24 .774 23.2 0 22.222 0h.003z"/>
                </svg>
            </a>
        @endif
        
        @if(in_array('whatsapp', $platforms))
            <a href="{{ $whatsappUrl }}" 
               target="_blank" 
               rel="noopener"
               class="inline-flex items-center justify-center {{ $buttonSize }} {{ $styleClasses }} rounded-lg bg-green-500 text-white hover:bg-green-600"
               title="{{ __('app.social.share_whatsapp') }}"
               data-share-platform="whatsapp"
               data-share-url="{{ $shareUrl }}">
                <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24">
                    <path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.890-5.335 11.893-11.893A11.821 11.821 0 0020.893 3.085"/>
                </svg>
            </a>
        @endif
        
        @if(in_array('telegram', $platforms))
            <a href="{{ $telegramUrl }}" 
               target="_blank" 
               rel="noopener"
               class="inline-flex items-center justify-center {{ $buttonSize }} {{ $styleClasses }} rounded-lg bg-sky-400 text-white hover:bg-sky-500"
               title="{{ __('app.social.share_telegram') }}"
               data-share-platform="telegram"
               data-share-url="{{ $shareUrl }}">
                <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24">
                    <path d="M11.944 0A12 12 0 0 0 0 12a12 12 0 0 0 12 12 12 12 0 0 0 12-12A12 12 0 0 0 12 0a12 12 0 0 0-.056 0zm4.962 7.224c.1-.002.321.023.465.14a.506.506 0 0 1 .171.325c.016.093.036.306.02.472-.18 1.898-.962 6.502-1.36 8.627-.168.9-.499 1.201-.82 1.23-.696.065-1.225-.46-1.9-.902-1.056-.693-1.653-1.124-2.678-1.8-1.185-.78-.417-1.21.258-1.91.177-.184 3.247-2.977 3.307-3.23.007-.032.014-.15-.056-.212s-.174-.041-.249-.024c-.106.024-1.793 1.14-5.061 3.345-.48.33-.913.49-1.302.48-.428-.008-1.252-.241-1.865-.44-.752-.245-1.349-.374-1.297-.789.027-.216.325-.437.893-.663 3.498-1.524 5.83-2.529 6.998-3.014 3.332-1.386 4.025-1.627 4.476-1.635z"/>
                </svg>
            </a>
        @endif
        
        @if(in_array('copy', $platforms))
            <button type="button"
                    onclick="copyToClipboard('{{ $shareUrl }}')"
                    class="inline-flex items-center justify-center {{ $buttonSize }} {{ $styleClasses }} rounded-lg bg-gray-600 text-white hover:bg-gray-700"
                    title="{{ __('app.social.copy_link') }}"
                    data-share-platform="copy"
                    data-share-url="{{ $shareUrl }}">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z"/>
                </svg>
            </button>
        @endif
        
    </div>
</div>

@once
@push('scripts')
<script>
// Copy to clipboard functionality
function copyToClipboard(text) {
    if (navigator.clipboard && window.isSecureContext) {
        navigator.clipboard.writeText(text).then(() => {
            showToast('{{ __("app.social.link_copied") }}', 'success');
        }).catch(() => {
            fallbackCopyTextToClipboard(text);
        });
    } else {
        fallbackCopyTextToClipboard(text);
    }
}

function fallbackCopyTextToClipboard(text) {
    const textArea = document.createElement("textarea");
    textArea.value = text;
    textArea.style.top = "0";
    textArea.style.left = "0";
    textArea.style.position = "fixed";
    textArea.style.opacity = "0";
    document.body.appendChild(textArea);
    textArea.focus();
    textArea.select();
    
    try {
        document.execCommand('copy');
        showToast('{{ __("app.social.link_copied") }}', 'success');
    } catch (err) {
        console.error('Fallback: Could not copy text', err);
        showToast('{{ __("app.social.copy_failed") }}', 'error');
    }
    
    document.body.removeChild(textArea);
}

// Simple toast notification
function showToast(message, type = 'info') {
    const toast = document.createElement('div');
    toast.className = `fixed bottom-4 right-4 px-6 py-3 rounded-lg shadow-lg z-50 transition-all duration-300 transform translate-y-full opacity-0 ${
        type === 'success' ? 'bg-green-500 text-white' : 
        type === 'error' ? 'bg-red-500 text-white' : 
        'bg-blue-500 text-white'
    }`;
    toast.textContent = message;
    
    document.body.appendChild(toast);
    
    // Animate in
    setTimeout(() => {
        toast.classList.remove('translate-y-full', 'opacity-0');
    }, 100);
    
    // Animate out and remove
    setTimeout(() => {
        toast.classList.add('translate-y-full', 'opacity-0');
        setTimeout(() => {
            if (toast.parentNode) {
                toast.parentNode.removeChild(toast);
            }
        }, 300);
    }, 3000);
}

// Track social share clicks
document.addEventListener('click', function(e) {
    const shareButton = e.target.closest('[data-share-platform]');
    if (shareButton) {
        const platform = shareButton.getAttribute('data-share-platform');
        const url = shareButton.getAttribute('data-share-url');
        
        // Analytics tracking if available
        if (typeof gtag !== 'undefined') {
            gtag('event', 'share', {
                'method': platform,
                'content_type': 'url',
                'item_id': url
            });
        }
        
        console.log(`Shared via ${platform}: ${url}`);
    }
});
</script>
@endpush
@endonce
