<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Lets editors curate the printed journal explicitly instead of it always
     * being auto-picked: `show_at_journal` flags an article for inclusion in
     * the next PDF edition, `is_journal_lead` marks it as the front-page "Une".
     */
    public function up(): void
    {
        Schema::table('news', function (Blueprint $table) {
            $table->boolean('show_at_journal')->default(0)->after('show_at_popular');
            $table->boolean('is_journal_lead')->default(0)->after('show_at_journal');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('news', function (Blueprint $table) {
            $table->dropColumn(['show_at_journal', 'is_journal_lead']);
        });
    }
};
