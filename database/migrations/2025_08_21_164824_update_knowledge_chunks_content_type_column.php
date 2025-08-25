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
        Schema::table('knowledge_chunks', function (Blueprint $table) {
            // content_type sütununu enum'dan varchar(100)'e değiştir
            $table->string('content_type', 100)->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('knowledge_chunks', function (Blueprint $table) {
            // Geri dönüş için enum'a çevir
            $table->enum('content_type', ['product', 'faq', 'blog', 'review', 'category', 'general'])->change();
        });
    }
};
