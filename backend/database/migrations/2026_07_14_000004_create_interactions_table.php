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
        Schema::create('interactions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('post_id')->constrained()->cascadeOnDelete();
            $table->foreignId('author_id')->constrained('users')->cascadeOnDelete();
            $table->string('type', 20);
            $table->timestampTz('created_at')->useCurrent();
        });

        DB::statement("ALTER TABLE interactions ADD CONSTRAINT interactions_type_check CHECK (type IN ('view', 'reply', 'reaction', 'share'))");

        DB::statement('CREATE INDEX idx_interactions_user_author ON interactions (user_id, author_id, created_at DESC)');
        DB::statement('CREATE INDEX idx_interactions_post_type ON interactions (post_id, type)');
        DB::statement('CREATE INDEX idx_interactions_created ON interactions (created_at)');

        DB::statement("CREATE UNIQUE INDEX idx_interactions_unique_strong_signal ON interactions (user_id, post_id, type) WHERE type IN ('reaction', 'share')");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('interactions');
    }
};
