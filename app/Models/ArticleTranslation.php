<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Str;

/**
 * Article Translation Model
 * 
 * Stores content for articles in different languages.
 * Each article can have multiple translations.
 */
class ArticleTranslation extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'article_id',
        'locale',
        'title',
        'excerpt',
        'content',
        'meta_title',
        'meta_description',
        'meta_keywords',
        'translation_status',
        'translated_by',
        'translated_at',
        'translation_notes',
        'word_count',
        'translation_quality_score',
    ];

    /**
     * The attributes that should be cast.
     */
    protected $casts = [
        'meta_keywords' => 'array',
        'translated_at' => 'datetime',
        'word_count' => 'integer',
        'translation_quality_score' => 'decimal:2',
    ];

    /**
     * The accessors to append to the model's array form.
     */
    protected $appends = [
        'reading_time',
        'content_preview',
        'is_approved',
    ];

    /**
     * Get the article that owns this translation.
     */
    public function article(): BelongsTo
    {
        return $this->belongsTo(Article::class);
    }

    /**
     * Get the user who translated this content.
     */
    public function translator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'translated_by');
    }

    /**
     * Get estimated reading time based on word count.
     */
    public function getReadingTimeAttribute(): int
    {
        if ($this->word_count) {
            return max(1, (int) ceil($this->word_count / 200));
        }

        // Fallback: calculate from content
        $wordCount = str_word_count(strip_tags($this->content));
        return max(1, (int) ceil($wordCount / 200));
    }

    /**
     * Get a preview of the content (first 200 characters).
     */
    public function getContentPreviewAttribute(): string
    {
        if (!$this->content) {
            return '';
        }

        $cleanContent = strip_tags($this->content);
        return Str::limit($cleanContent, 200);
    }

    /**
     * Check if translation is approved.
     */
    public function getIsApprovedAttribute(): bool
    {
        return $this->translation_status === 'approved';
    }

    /**
     * Get SEO meta title (falls back to title).
     */
    public function getSeoTitleAttribute(): string
    {
        return $this->meta_title ?: $this->title;
    }

    /**
     * Get SEO meta description (falls back to excerpt).
     */
    public function getSeoDescriptionAttribute(): string
    {
        return $this->meta_description ?: $this->excerpt ?: $this->content_preview;
    }

    /**
     * Scope query to filter by locale.
     */
    public function scopeForLocale(Builder $query, string $locale): Builder
    {
        return $query->where('locale', $locale);
    }

    /**
     * Scope query to only include approved translations.
     */
    public function scopeApproved(Builder $query): Builder
    {
        return $query->where('translation_status', 'approved');
    }

    /**
     * Scope query to only include auto-translated content.
     */
    public function scopeAutoTranslated(Builder $query): Builder
    {
        return $query->where('translation_status', 'auto_translated');
    }

    /**
     * Scope query to only include content needing review.
     */
    public function scopeNeedsReview(Builder $query): Builder
    {
        return $query->whereIn('translation_status', ['auto_translated', 'human_reviewed']);
    }

    /**
     * Update word count based on content.
     */
    public function updateWordCount(): void
    {
        $wordCount = str_word_count(strip_tags($this->content));
        $this->update(['word_count' => $wordCount]);
    }

    /**
     * Mark translation as approved.
     */
    public function approve(User $user = null): void
    {
        $this->update([
            'translation_status' => 'approved',
            'translated_by' => $user?->id,
            'translated_at' => now(),
        ]);
    }

    /**
     * Mark translation as needing review.
     */
    public function markForReview(string $notes = null): void
    {
        $this->update([
            'translation_status' => 'human_reviewed',
            'translation_notes' => $notes,
        ]);
    }

    /**
     * Get quality status with color coding.
     */
    public function getQualityStatusAttribute(): array
    {
        $statusMap = [
            'pending' => ['label' => 'Pending', 'color' => 'gray'],
            'auto_translated' => ['label' => 'Auto-translated', 'color' => 'blue'],
            'human_reviewed' => ['label' => 'Reviewed', 'color' => 'yellow'],
            'approved' => ['label' => 'Approved', 'color' => 'green'],
        ];

        return $statusMap[$this->translation_status] ?? $statusMap['pending'];
    }

    /**
     * Generate meta description from content if empty.
     */
    public function generateMetaDescription(): void
    {
        if (empty($this->meta_description) && $this->content) {
            $cleanContent = strip_tags($this->content);
            $metaDescription = Str::limit($cleanContent, 160);
            $this->update(['meta_description' => $metaDescription]);
        }
    }

    /**
     * Check if translation is complete (has all required fields).
     */
    public function isComplete(): bool
    {
        return !empty($this->title) 
            && !empty($this->content) 
            && !empty($this->excerpt);
    }

    /**
     * Get completion percentage for this translation.
     */
    public function getCompletionPercentage(): int
    {
        $fields = ['title', 'excerpt', 'content', 'meta_title', 'meta_description'];
        $completed = 0;

        foreach ($fields as $field) {
            if (!empty($this->$field)) {
                $completed++;
            }
        }

        return round(($completed / count($fields)) * 100);
    }
}