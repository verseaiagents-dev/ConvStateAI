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
        Schema::create('widget_actions', function (Blueprint $table) {
            $table->id();
            $table->string('siparis_durumu_endpoint')->nullable();
            $table->string('kargo_durumu_endpoint')->nullable();
            $table->enum('http_action', ['GET'])->default('GET');
            $table->unsignedBigInteger('widget_customization_id');
            $table->timestamps();
            
            $table->foreign('widget_customization_id')->references('id')->on('widget_customizations')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('widget_actions');
    }
};
