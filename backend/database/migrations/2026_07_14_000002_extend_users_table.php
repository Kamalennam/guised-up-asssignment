<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('username', 50)->unique()->after('name');
            $table->string('avatar_url', 500)->nullable()->after('password');
            $table->unsignedInteger('interest_interaction_count')->default(0)->after('avatar_url');
        });

        DB::statement('ALTER TABLE users ADD COLUMN interest_embedding vector(384) NULL');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn([
                'username',
                'avatar_url',
                'interest_interaction_count',
            ]);
        });

        DB::statement('ALTER TABLE users DROP COLUMN IF EXISTS interest_embedding');
    }
};
