<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * 'tag' = the RSS item's own <category> matched a known rubrique
     * directly (reliable). 'keyword' / 'default' = fell back to a
     * title-keyword guess or the feed's configured category with no real
     * signal (unreliable). Gemini's free-tier classify() quota is only
     * 20 requests/day per key, so AutoPublishCollectedNews spends it only
     * on the unreliable cases instead of reclassifying every article.
     */
    public function up(): void
    {
        Schema::table('collected_articles', function (Blueprint $table) {
            $table->enum('category_confidence', ['tag', 'keyword', 'default'])
                ->default('default')
                ->after('category_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('collected_articles', function (Blueprint $table) {
            $table->dropColumn('category_confidence');
        });
    }
};
