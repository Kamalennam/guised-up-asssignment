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
        Schema::create('posts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->text('text');
            $table->string('image_url', 500)->nullable();
            $table->decimal('authenticity_score', 5, 4);
            $table->jsonb('authenticity_breakdown');
            $table->string('embedding_model', 100)->nullable();
            $table->string('embedding_status', 20)->default('pending');
            $table->string('content_hash', 64);
            $table->jsonb('metadata_json')->nullable();
            $table->timestampsTz();
            $table->softDeletesTz();
        });

        DB::statement('ALTER TABLE posts ADD COLUMN embedding vector(384) NULL');

        DB::statement("ALTER TABLE posts ADD CONSTRAINT posts_embedding_status_check CHECK (embedding_status IN ('pending', 'completed', 'failed'))");

        DB::statement('CREATE INDEX idx_posts_created_at ON posts (created_at DESC) WHERE deleted_at IS NULL');
        DB::statement('CREATE INDEX idx_posts_user_created ON posts (user_id, created_at DESC)');
        DB::statement('CREATE INDEX idx_posts_content_hash ON posts (user_id, content_hash)');

        DB::statement('CREATE INDEX idx_posts_embedding_hnsw ON posts USING hnsw (embedding vector_cosine_ops) WITH (m = 16, ef_construction = 64)');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::statement('DROP INDEX IF EXISTS idx_posts_embedding_hnsw');
        DB::statement('DROP INDEX IF EXISTS idx_posts_content_hash');
        DB::statement('DROP INDEX IF EXISTS idx_posts_user_created');
        DB::statement('DROP INDEX IF EXISTS idx_posts_created_at');

        Schema::dropIfExists('posts');
    }
};
