<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Builder;

class Comment extends Model
{
    use HasFactory;
    /**
     * Indicates if the model should be timestamped.
     * Comment only has created_at, no updated_at.
     *
     * @var bool
     */
    public $timestamps = false;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'recipe_version_id',
        'user_id',
        'content',
        'is_ai',
        'result_version_id',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'is_ai' => 'boolean',
            'created_at' => 'datetime',
        ];
    }

    /**
     * Get the recipe version this comment belongs to.
     */
    public function recipeVersion(): BelongsTo
    {
        return $this->belongsTo(RecipeVersion::class);
    }

    /**
     * Get the user who made this comment (null if AI).
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the result version that was created from this comment.
     */
    public function resultVersion(): BelongsTo
    {
        return $this->belongsTo(RecipeVersion::class, 'result_version_id');
    }

    /**
     * Scope to filter AI comments.
     */
    public function scopeIsAi(Builder $query): Builder
    {
        return $query->where('is_ai', true);
    }

    /**
     * Scope to filter user comments.
     */
    public function scopeIsUser(Builder $query): Builder
    {
        return $query->where('is_ai', false);
    }

    /**
     * Boot the model and automatically set created_at.
     */
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($model) {
            if (!$model->created_at) {
                $model->created_at = now();
            }
        });
    }
}
