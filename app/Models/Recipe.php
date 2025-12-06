<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Recipe extends Model
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'user_id',
        'name',
        'description',
    ];

    /**
     * Get the user that owns the recipe.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get all versions for this recipe.
     */
    public function versions(): HasMany
    {
        return $this->hasMany(RecipeVersion::class);
    }

    /**
     * Get the latest version of this recipe.
     */
    public function latestVersion()
    {
        return $this->hasOne(RecipeVersion::class)->latestOfMany('version_number');
    }

    /**
     * Get the latest version number for this recipe.
     */
    public function getLatestVersionNumber(): int
    {
        return $this->versions()->max('version_number') ?? 0;
    }
}
