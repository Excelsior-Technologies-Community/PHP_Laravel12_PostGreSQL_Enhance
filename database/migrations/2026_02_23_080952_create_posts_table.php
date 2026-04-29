<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration {
    public function up(): void
    {
        // Drop trigger & function if they exist (safe)
        DB::statement('DROP TRIGGER IF EXISTS posts_searchable_update ON posts;');
        DB::statement('DROP FUNCTION IF EXISTS posts_searchable_trigger();');

        Schema::create('posts', function (Blueprint $table) {

            // UUID Primary Key
            $table->uuid('id')->primary();

            // Basic Fields
            $table->string('title');
            $table->text('content');

            // Status column
            $table->string('status')->default('draft');

            // JSONB column
            $table->jsonb('metadata')->nullable();

            // Full Text Search column
            $table->tsvector('searchable')->nullable();

            // Generated slug
            $table->string('slug')->storedAs('lower(title)');

            // ✅ NEW: Like system
            $table->integer('likes')->default(0);

            // ✅ NEW: Soft delete (Trash)
            $table->softDeletes();

            $table->timestamps();

            // GIN index for JSONB
            $table->index('metadata', null, 'gin');

            // GIN index for full-text search
            $table->index('searchable', null, 'gin');
        });

        // Expression index
        DB::statement('
            CREATE INDEX posts_title_lower_idx
            ON posts (lower(title));
        ');

        // Partial index
        DB::statement("
            CREATE INDEX posts_published_idx
            ON posts (status)
            WHERE status = 'published';
        ");

        // Full Text Search Function
        DB::statement("
            CREATE FUNCTION posts_searchable_trigger() RETURNS trigger AS $$
            BEGIN
                NEW.searchable := to_tsvector('english', coalesce(NEW.title,'') || ' ' || coalesce(NEW.content,''));
                RETURN NEW;
            END
            $$ LANGUAGE plpgsql;
        ");

        // Trigger
        DB::statement("
            CREATE TRIGGER posts_searchable_update
            BEFORE INSERT OR UPDATE
            ON posts
            FOR EACH ROW
            EXECUTE FUNCTION posts_searchable_trigger();
        ");
    }

    public function down(): void
    {
        DB::statement('DROP TRIGGER IF EXISTS posts_searchable_update ON posts;');
        DB::statement('DROP FUNCTION IF EXISTS posts_searchable_trigger();');

        DB::statement('DROP INDEX IF EXISTS posts_title_lower_idx;');
        DB::statement('DROP INDEX IF EXISTS posts_published_idx;');

        Schema::dropIfExists('posts');
    }
};