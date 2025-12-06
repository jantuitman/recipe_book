<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\RecipeController;
use App\Http\Controllers\ChatController;
use App\Http\Controllers\CommentController;
use App\Http\Controllers\SettingsController;
use App\Http\Controllers\Api\RecipeParserController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::middleware('auth')->group(function () {
    // Dashboard route - redirect to recipes index
    Route::get('/dashboard', [RecipeController::class, 'index'])->name('dashboard');

    // Recipe routes
    Route::resource('recipes', RecipeController::class);

    // Recipe version routes
    Route::get('/recipes/{recipe}/history', [RecipeController::class, 'history'])->name('recipes.history');
    Route::get('/recipes/{recipe}/versions/{version}', [RecipeController::class, 'showVersion'])->name('recipes.versions.show');

    // Recipe feedback routes
    Route::get('/recipes/{recipe}/feedback', [RecipeController::class, 'feedback'])->name('recipes.feedback');
    Route::post('/recipes/{recipe}/feedback', [RecipeController::class, 'processFeedback'])->name('recipes.feedback.process');
    Route::post('/recipes/{recipe}/apply-suggestions', [RecipeController::class, 'applySuggestions'])->name('recipes.apply-suggestions');

    // Comment routes
    Route::post('/recipes/{recipe}/versions/{version}/comments', [CommentController::class, 'store'])->name('comments.store');

    // Chat routes
    Route::get('/chat', [ChatController::class, 'index'])->name('chat');
    Route::get('/chat/messages', [ChatController::class, 'messages'])->name('chat.messages');
    Route::post('/chat', [ChatController::class, 'sendMessage'])->name('chat.send');

    // AI API routes
    Route::post('/ai/parse-recipe', [RecipeParserController::class, 'parse'])->name('ai.parse-recipe');

    // Profile routes
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    // Settings routes
    Route::get('/settings', [SettingsController::class, 'edit'])->name('settings.edit');
    Route::patch('/settings', [SettingsController::class, 'update'])->name('settings.update');
});

require __DIR__.'/auth.php';
