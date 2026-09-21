<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddFacebookFieldsToNewsTable extends Migration
{
    public function up()
    {
        Schema::table('news', function (Blueprint $table) {
            // Editor-controlled curation flag ("Publier sur Facebook") —
            // mirrors show_at_journal/is_breaking_news: a human can flag a
            // specific article as the one to post; the daily Facebook
            // command prefers these and only falls back to picking an
            // AI-authored article itself when a rubrique has none flagged.
            $table->boolean('post_to_facebook')->default(0)->after('show_at_journal');

            // Null until actually posted — lets the command skip articles
            // already shared and pick a fresh one per rubrique per day.
            $table->timestamp('posted_to_facebook_at')->nullable()->after('post_to_facebook');
        });
    }

    public function down()
    {
        Schema::table('news', function (Blueprint $table) {
            $table->dropColumn(['post_to_facebook', 'posted_to_facebook_at']);
        });
    }
}
