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
        Schema::dropIfExists('campaigns');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::create('campaigns', function (Blueprint $table) {
            $table->id();
            $table->string('intent');
            $table->enum('pattern', [
                'value_stack',
                'scarcity', 
                'bundle',
                'gamification',
                'social_proof',
                'lightning_deal',
                'subscribe_and_save'
            ]);
            $table->json('data');
            $table->text('message');
            $table->boolean('status')->default(true);
            
            // Widget kampanya alanları
            $table->string('title')->nullable();
            $table->string('cta_button')->nullable();
            $table->boolean('is_active')->default(false);
            $table->timestamp('start_date')->nullable();
            $table->timestamp('end_date')->nullable();
            $table->integer('priority')->default(0);
            
            $table->timestamps();
        });
    }
};
