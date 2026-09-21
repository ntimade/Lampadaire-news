<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Staging table for the RSS "veille" (watch) job. It never touches the
     * public `news` table directly — items collected here are raw external
     * content and must be reviewed/rewritten by an editor (via "Convertir en
     * brouillon") before they can become a real article. This keeps sourced
     * material clearly separate from published content and avoids
     * republishing scraped text verbatim.
     */
    public function up(): void
    {
        Schema::create('collected_articles', function (Blueprint $table) {
            $table->id();
            $table->foreignId('category_id')->nullable()->constrained('categories')->nullOnDelete();
            $table->string('source_name');
            $table->text('source_url');
            $table->string('source_url_hash', 64)->unique();
            $table->string('title');
            $table->text('summary')->nullable();
            $table->timestamp('published_at')->nullable();
            $table->timestamp('fetched_at')->useCurrent();
            $table->enum('status', ['new', 'converted', 'dismissed'])->default('new');
            $table->foreignId('converted_news_id')->nullable()->constrained('news')->nullOnDelete();
            $table->timestamps();

            $table->index('status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('collected_articles');
    }
};
