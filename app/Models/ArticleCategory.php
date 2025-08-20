<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Builder;

/**
 * Article Category Model
 * 
 * Represents content categories for the editorial system.
 * Categories are multilingual and can be used in navigation menus.
 */
class ArticleCategory extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'slug',
        'name_it',
        'name_en',
        'name_de',
        'name_es',
        'description_it',
        'description_en',
        'description_de',
        'description_es',
        'sort_order',
        'active',
        'icon',
        'color',
    ];

    /**
     * The attributes that should be cast.
     */
    protected $casts = [
        'active' => 'boolean',
        'sort_order' => 'integer',
    ];

    /**
     * Get all articles in this category.
     */
    public function articles(): HasMany
    {
        return $this->hasMany(Article::class, 'category_id');
    }

    /**
     * Get published articles in this category.
     */
    public function publishedArticles(): HasMany
    {
        return $this->articles()
            ->where('status', 'published')
            ->whereNotNull('published_at')
            ->where('published_at', '<=', now())
            ->orderBy('published_at', 'desc');
    }

    /**
     * Get the category name for the current locale.
     */
    public function getLocalizedNameAttribute(): string
    {
        $locale = app()->getLocale();
        return $this->{"name_{$locale}"} ?? $this->name_en ?? $this->name_it;
    }

    /**
     * Get the category description for the current locale.
     */
    public function getLocalizedDescriptionAttribute(): ?string
    {
        $locale = app()->getLocale();
        return $this->{"description_{$locale}"} ?? $this->description_en ?? $this->description_it;
    }

    /**
     * Get the URL for this category.
     */
    public function getUrlAttribute(): string
    {
        $locale = app()->getLocale();
        return route('localized.articles.category', [
            'locale' => $locale,
            'category' => $this->slug
        ]);
    }

    /**
     * Scope query to only include active categories.
     */
    public function scopeActive(Builder $query): Builder
    {
        return $query->where('active', true);
    }

    /**
     * Scope query to order by sort order.
     */
    public function scopeOrdered(Builder $query): Builder
    {
        return $query->orderBy('sort_order')->orderBy('name_it');
    }

    /**
     * Scope query to include article counts.
     */
    public function scopeWithArticleCounts(Builder $query): Builder
    {
        return $query->withCount([
            'articles',
            'publishedArticles as published_count'
        ]);
    }

    /**
     * Get name for a specific locale.
     */
    public function getNameForLocale(string $locale): string
    {
        return $this->{"name_{$locale}"} ?? $this->name_en ?? $this->name_it;
    }

    /**
     * Get description for a specific locale.
     */
    public function getDescriptionForLocale(string $locale): ?string
    {
        return $this->{"description_{$locale}"} ?? $this->description_en ?? $this->description_it;
    }

    /**
     * Check if category has localized content.
     */
    public function hasLocalization(string $locale): bool
    {
        return !empty($this->{"name_{$locale}"});
    }
}