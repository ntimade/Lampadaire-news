<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * `created_at` reflects when a draft was first saved, not when it actually
     * went live — an article can sit unapproved for days before an admin
     * publishes it. `published_at` is stamped the moment it is first approved,
     * so ordering by it (instead of created_at) gives a true publication order.
     */
    public function up(): void
    {
        Schema::table('news', function (Blueprint $table) {
            $table->timestamp('published_at')->nullable()->after('is_approved');
        });

        // Backfill: already-approved articles are treated as published when they were created.
        DB::table('news')->where('is_approved', 1)->update([
            'published_at' => DB::raw('created_at'),
        ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('news', function (Blueprint $table) {
            $table->dropColumn('published_at');
        });
    }
};
