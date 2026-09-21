<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddPrintExcerptToNewsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('news', function (Blueprint $table) {
            // Gemini-cleaned, coherent excerpt used by the printable PDF
            // newspaper — generated once per article and cached here so a
            // re-download never re-spends the dedicated journal API key's
            // daily quota. Null until first generated, or if Gemini failed
            // (the newspaper then falls back to the raw content).
            $table->text('print_excerpt')->nullable()->after('content');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('news', function (Blueprint $table) {
            $table->dropColumn('print_excerpt');
        });
    }
}
