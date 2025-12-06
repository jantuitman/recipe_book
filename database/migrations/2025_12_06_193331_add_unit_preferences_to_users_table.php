<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->enum('volume_unit', ['ml', 'cups', 'fl_oz'])->default('ml')->after('remember_token');
            $table->enum('weight_unit', ['g', 'oz', 'lbs'])->default('g')->after('volume_unit');
            $table->enum('time_format', ['min', 'hr_min'])->default('min')->after('weight_unit');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['volume_unit', 'weight_unit', 'time_format']);
        });
    }
};
