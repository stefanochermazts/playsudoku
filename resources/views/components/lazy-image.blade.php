@props([
    'src' => '',
    'alt' => '',
    'width' => null,
    'height' => null,
    'placeholder' => null,
    'loading' => 'lazy',
    'sizes' => null,
    'srcset' => null,
    'class' => '',
    'fallback' => null,
    'eager' => false,
    'fadeIn' => true
])

@php
    // Se eager è true, usa loading="eager" per immagini above-the-fold
    $loadingAttr = $eager ? 'eager' : $loading;
    
    // Placeholder default: un'immagine trasparente 1x1 base64
    $defaultPlaceholder = 'data:image/svg+xml;base64,' . base64_encode(
        '<svg width="' . ($width ?? 400) . '" height="' . ($height ?? 300) . '" xmlns="http://www.w3.org/2000/svg"><rect width="100%" height="100%" fill="#f3f4f6"/><text x="50%" y="50%" text-anchor="middle" dy="0.3em" fill="#9ca3af" font-family="sans-serif" font-size="14">Loading...</text></svg>'
    );
    
    $placeholderSrc = $placeholder ?? $defaultPlaceholder;
    
    // Generiamo un ID unico per questa immagine
    $imageId = 'lazy-img-' . uniqid();
    
    // CSS classes per animazione fade-in
    $transitionClass = $fadeIn ? 'transition-opacity duration-300' : '';
    $initialOpacity = $eager ? 'opacity-100' : 'opacity-0';
@endphp

<img
    id="{{ $imageId }}"
    {{ $attributes->merge(['class' => "lazy-image {$transitionClass} {$initialOpacity} {$class}"]) }}
    src="{{ $eager ? $src : $placeholderSrc }}"
    data-src="{{ $src }}"
    @if($srcset) data-srcset="{{ $srcset }}" @endif
    @if($sizes) data-sizes="{{ $sizes }}" @endif
    alt="{{ $alt }}"
    @if($width) width="{{ $width }}" @endif
    @if($height) height="{{ $height }}" @endif
    loading="{{ $loadingAttr }}"
    decoding="async"
    @if(!$eager) onload="this.classList.add('opacity-100')" @endif
    onerror="this.src='{{ $fallback ?? $placeholderSrc }}'; this.onerror=null;"
/>

@if(!$eager)
@once
@push('scripts')
<script>
// Lazy Loading con Intersection Observer
document.addEventListener('DOMContentLoaded', function() {
    if ('IntersectionObserver' in window) {
        const imageObserver = new IntersectionObserver((entries, observer) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    const img = entry.target;
                    
                    // Sostituisci src con data-src
                    if (img.dataset.src) {
                        img.src = img.dataset.src;
                        img.removeAttribute('data-src');
                    }
                    
                    // Sostituisci srcset se presente
                    if (img.dataset.srcset) {
                        img.srcset = img.dataset.srcset;
                        img.removeAttribute('data-srcset');
                    }
                    
                    // Sostituisci sizes se presente
                    if (img.dataset.sizes) {
                        img.sizes = img.dataset.sizes;
                        img.removeAttribute('data-sizes');
                    }
                    
                    // Rimuovi la classe lazy-image e aggiungi fade-in
                    img.classList.remove('lazy-image');
                    img.classList.add('opacity-100');
                    
                    // Smetti di osservare questa immagine
                    observer.unobserve(img);
                }
            });
        }, {
            // Carica l'immagine quando è a 50px dal viewport
            rootMargin: '50px 0px',
            threshold: 0.01
        });

        // Osserva tutte le immagini lazy
        document.querySelectorAll('.lazy-image').forEach(img => {
            imageObserver.observe(img);
        });
    } else {
        // Fallback per browser senza IntersectionObserver
        document.querySelectorAll('.lazy-image').forEach(img => {
            if (img.dataset.src) {
                img.src = img.dataset.src;
                img.removeAttribute('data-src');
            }
            if (img.dataset.srcset) {
                img.srcset = img.dataset.srcset;
                img.removeAttribute('data-srcset');
            }
            if (img.dataset.sizes) {
                img.sizes = img.dataset.sizes;
                img.removeAttribute('data-sizes');
            }
            img.classList.add('opacity-100');
        });
    }
});

// Performance: Preload critical images on hover (prefetch)
document.addEventListener('mouseover', function(e) {
    const link = e.target.closest('a[href]');
    if (link && !link.dataset.prefetched) {
        link.dataset.prefetched = 'true';
        
        // Crea un prefetch link per la pagina di destinazione
        const prefetchLink = document.createElement('link');
        prefetchLink.rel = 'prefetch';
        prefetchLink.href = link.href;
        document.head.appendChild(prefetchLink);
    }
}, { passive: true });

// Critical Web Vitals: Reduce CLS with proper image dimensions
window.addEventListener('load', function() {
    // Report Core Web Vitals to Analytics se disponibile
    if (typeof gtag !== 'undefined') {
        // Invia metriche a Google Analytics
        new PerformanceObserver((entryList) => {
            for (const entry of entryList.getEntries()) {
                gtag('event', 'web_vital', {
                    name: entry.name,
                    value: Math.round(entry.name === 'CLS' ? entry.value * 1000 : entry.value),
                    event_category: 'Web Vitals',
                    non_interaction: true,
                });
            }
        }).observe({ type: 'layout-shift', buffered: true });
    }
});
</script>
@endpush
@endonce
@endif
