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
        Schema::table('courses', function (Blueprint $table) {
            $table->foreignId('group_id')->nullable()->after('professor_id')->constrained()->restrictOnDelete();
        });

        Schema::dropIfExists('course_student');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::create('course_student', function (Blueprint $table) {
            $table->foreignId('course_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->primary(['course_id', 'user_id']);
        });

        Schema::table('courses', function (Blueprint $table) {
            $table->dropConstrainedForeignId('group_id');
        });
    }
};
