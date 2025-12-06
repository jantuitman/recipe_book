<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Builder;

class ChatMessage extends Model
{
    use HasFactory;
    /**
     * Indicates if the model should be timestamped.
     * ChatMessage only has created_at, no updated_at.
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
        'user_id',
        'role',
        'content',
        'recipe_id',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'created_at' => 'datetime',
        ];
    }

    /**
     * Get the user who sent this message.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the recipe that was created from this chat (if any).
     */
    public function recipe(): BelongsTo
    {
        return $this->belongsTo(Recipe::class);
    }

    /**
     * Scope to filter by role.
     */
    public function scopeRole(Builder $query, string $role): Builder
    {
        return $query->where('role', $role);
    }

    /**
     * Scope to get user messages.
     */
    public function scopeUserMessages(Builder $query): Builder
    {
        return $query->where('role', 'user');
    }

    /**
     * Scope to get assistant messages.
     */
    public function scopeAssistantMessages(Builder $query): Builder
    {
        return $query->where('role', 'assistant');
    }

    /**
     * Scope to get messages chronologically.
     */
    public function scopeChronological(Builder $query): Builder
    {
        return $query->orderBy('created_at', 'asc');
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
