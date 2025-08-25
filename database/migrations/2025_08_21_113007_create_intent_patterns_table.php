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
        Schema::create('intent_patterns', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('site_id')->default(1);
            $table->string('intent_name', 255);
            $table->string('intent_category', 100);
            $table->text('description')->nullable();
            $table->json('keywords'); // ["arama", "bul", "göster"]
            $table->json('synonyms')->nullable(); // ["ürün", "item", "product"]
            $table->decimal('confidence_threshold', 3, 2)->default(0.70);
            $table->text('response_template')->nullable();
            $table->boolean('is_active')->default(true);
            $table->integer('usage_count')->default(0);
            $table->decimal('success_rate', 5, 2)->default(0.00);
            $table->timestamps();
            
            $table->index(['site_id', 'intent_name']);
            $table->index('intent_category');
            $table->index('is_active');
            $table->index('confidence_threshold');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('intent_patterns');
    }
};
