<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Str;
use Carbon\Carbon;

/**
 * Article Model
 * 
 * Represents articles in the editorial system.
 * Articles have translations in multiple languages.
 */
class Article extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'category_id',
        'slug',
        'status',
        'featured_image',
        'tags',
        'published_at',
        'reading_time_minutes',
        'featured',
        'created_by',
        'updated_by',
        'views_count',
        'last_viewed_at',
        'translation_status',
        'auto_translate',
    ];

    /**
     * The attributes that should be cast.
     */
    protected $casts = [
        'tags' => 'array',
        'published_at' => 'datetime',
        'last_viewed_at' => 'datetime',
        'featured' => 'boolean',
        'auto_translate' => 'boolean',
        'translation_status' => 'array',
        'views_count' => 'integer',
        'reading_time_minutes' => 'integer',
    ];

    /**
     * The accessors to append to the model's array form.
     */
    protected $appends = [
        'is_published',
        'excerpt_for_locale',
        'title_for_locale',
        'url',
        'featured_image_url',
    ];

    /**
     * Get the category that owns the article.
     */
    public function category(): BelongsTo
    {
        return $this->belongsTo(ArticleCategory::class, 'category_id');
    }

    /**
     * Get the user who created the article.
     */
    public function author(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Get the user who last updated the article.
     */
    public function updatedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    /**
     * Get all translations for this article.
     */
    public function translations(): HasMany
    {
        return $this->hasMany(ArticleTranslation::class);
    }

    /**
     * Get translation for a specific locale.
     */
    public function translation(string $locale = null): ?ArticleTranslation
    {
        $locale = $locale ?? app()->getLocale();
        return $this->translations()->where('locale', $locale)->first();
    }

    /**
     * Get Italian translation (source language).
     */
    public function italianTranslation(): ?ArticleTranslation
    {
        return $this->translation('it');
    }

    /**
     * Check if article is published.
     */
    public function getIsPublishedAttribute(): bool
    {
        return $this->status === 'published' 
            && $this->published_at !== null 
            && $this->published_at <= now();
    }

    /**
     * Get the title for current locale.
     */
    public function getTitleForLocaleAttribute(): ?string
    {
        $translation = $this->translation();
        return $translation?->title;
    }

    /**
     * Get the excerpt for current locale.
     */
    public function getExcerptForLocaleAttribute(): ?string
    {
        $translation = $this->translation();
        return $translation?->excerpt;
    }

    /**
     * Get the content for current locale.
     */
    public function getContentForLocaleAttribute(): ?string
    {
        $translation = $this->translation();
        return $translation?->content;
    }

    /**
     * Get the URL for this article.
     */
    public function getUrlAttribute(): string
    {
        $locale = app()->getLocale();
        return route('localized.articles.show', [
            'locale' => $locale,
            'category' => $this->category->slug,
            'article' => $this->slug
        ]);
    }

    /**
     * Get the featured image URL.
     */
    public function getFeaturedImageUrlAttribute(): ?string
    {
        if (!$this->featured_image) {
            return null;
        }

        // If it's already a full URL, return as is
        if (Str::startsWith($this->featured_image, ['http://', 'https://'])) {
            return $this->featured_image;
        }

        // Otherwise, prepend storage URL
        return asset('storage/' . $this->featured_image);
    }

    /**
     * Scope query to only include published articles.
     */
    public function scopePublished(Builder $query): Builder
    {
        return $query->where('status', 'published')
            ->whereNotNull('published_at')
            ->where('published_at', '<=', now());
    }

    /**
     * Scope query to only include featured articles.
     */
    public function scopeFeatured(Builder $query): Builder
    {
        return $query->where('featured', true);
    }

    /**
     * Scope query to filter by category.
     */
    public function scopeInCategory(Builder $query, string $categorySlug): Builder
    {
        return $query->whereHas('category', function (Builder $q) use ($categorySlug) {
            $q->where('slug', $categorySlug);
        });
    }

    /**
     * Scope query to include translations for current locale.
     */
    public function scopeWithTranslation(Builder $query, string $locale = null): Builder
    {
        $locale = $locale ?? app()->getLocale();
        
        return $query->with(['translations' => function ($q) use ($locale) {
            $q->where('locale', $locale);
        }]);
    }

    /**
     * Scope query to search articles.
     */
    public function scopeSearch(Builder $query, string $term): Builder
    {
        return $query->whereHas('translations', function (Builder $q) use ($term) {
            $q->where('locale', app()->getLocale())
              ->where(function (Builder $subQuery) use ($term) {
                  $subQuery->where('title', 'ILIKE', "%{$term}%")
                           ->orWhere('excerpt', 'ILIKE', "%{$term}%")
                           ->orWhere('content', 'ILIKE', "%{$term}%");
              });
        });
    }

    /**
     * Increment the views count.
     */
    public function incrementViews(): void
    {
        $this->increment('views_count');
        $this->update(['last_viewed_at' => now()]);
    }

    /**
     * Calculate estimated reading time based on content.
     */
    public function calculateReadingTime(): int
    {
        $translation = $this->italianTranslation();
        if (!$translation || !$translation->content) {
            return 1;
        }

        // Average reading speed: 200 words per minute
        $wordCount = str_word_count(strip_tags($translation->content));
        return max(1, (int) ceil($wordCount / 200));
    }

    /**
     * Update reading time based on Italian content.
     */
    public function updateReadingTime(): void
    {
        $this->update(['reading_time_minutes' => $this->calculateReadingTime()]);
    }

    /**
     * Check if article has translation for locale.
     */
    public function hasTranslation(string $locale): bool
    {
        return $this->translations()->where('locale', $locale)->exists();
    }

    /**
     * Get translation completeness percentage.
     */
    public function getTranslationCompleteness(): array
    {
        $locales = ['it', 'en', 'de', 'es'];
        $completed = 0;
        $details = [];

        foreach ($locales as $locale) {
            $hasTranslation = $this->hasTranslation($locale);
            $details[$locale] = $hasTranslation;
            if ($hasTranslation) {
                $completed++;
            }
        }

        return [
            'percentage' => round(($completed / count($locales)) * 100),
            'completed' => $completed,
            'total' => count($locales),
            'details' => $details,
        ];
    }
}