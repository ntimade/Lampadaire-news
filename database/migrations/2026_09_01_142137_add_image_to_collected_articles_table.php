<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Local (downloaded, not hotlinked) copy of the source article's photo,
     * when the feed provides one — same relative-path convention as
     * News::image so a converted draft can reuse it directly.
     */
    public function up(): void
    {
        Schema::table('collected_articles', function (Blueprint $table) {
            $table->string('image')->nullable()->after('summary');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('collected_articles', function (Blueprint $table) {
            $table->dropColumn('image');
        });
    }
};
