<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('nodes', function (Blueprint $table) {
            $table->id();

            $table->foreignId('parent_id')
                ->nullable()
                ->constrained('nodes')
                ->cascadeOnDelete();

            $table->string('title');
            $table->text('description')->nullable();

            // путь в storage/app/public/...
            $table->string('image_path')->nullable();

            $table->unsignedInteger('sort_order')->default(0);
            $table->boolean('is_expanded')->default(false);

            $table->timestamps();

            $table->index('parent_id');
            $table->index(['parent_id', 'sort_order']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('nodes');
    }
};
