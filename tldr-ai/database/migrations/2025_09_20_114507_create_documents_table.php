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
        Schema::create('documents', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->string('filename'); // Original filename
            $table->string('stored_filename'); // Filename in Supabase (with timestamp)
            $table->string('file_path'); // Path in Supabase bucket
            $table->string('mime_type');
            $table->bigInteger('file_size')->nullable();
            $table->text('summary')->nullable(); // Store the generated summary
            $table->boolean('summary_generated')->default(false); // Track if summary was generated
            $table->string('public_url')->nullable(); // Supabase public URL
            $table->timestamps();

            $table->index(['user_id', 'created_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('documents');
    }
};