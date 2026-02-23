<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
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

            // Status column (for partial index)
            $table->string('status')->default('draft');

            // JSONB column (supports GIN index)
            $table->jsonb('metadata')->nullable();

            // Full Text Search column
            $table->tsvector('searchable')->nullable();

            // Generated slug column
            $table->string('slug')->storedAs('lower(title)');

            $table->timestamps();

            // GIN index for JSONB
            $table->index('metadata', null, 'gin');

            // GIN index for full-text search
            $table->index('searchable', null, 'gin');
        });

        // Expression index (lower title)
        DB::statement('
            CREATE INDEX posts_title_lower_idx
            ON posts (lower(title));
        ');

        // Partial index for published posts
        DB::statement("
            CREATE INDEX posts_published_idx
            ON posts (status)
            WHERE status = 'published';
        ");

        // Full Text Search Trigger Function
        DB::statement("
            CREATE FUNCTION posts_searchable_trigger() RETURNS trigger AS $$
            BEGIN
                NEW.searchable := to_tsvector('english', coalesce(NEW.title,'') || ' ' || coalesce(NEW.content,''));
                RETURN NEW;
            END
            $$ LANGUAGE plpgsql;
        ");

        // Trigger for full-text search
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
        // Drop trigger & function first
        DB::statement('DROP TRIGGER IF EXISTS posts_searchable_update ON posts;');
        DB::statement('DROP FUNCTION IF EXISTS posts_searchable_trigger();');

        // Drop expression and partial indexes
        DB::statement('DROP INDEX IF EXISTS posts_title_lower_idx;');
        DB::statement('DROP INDEX IF EXISTS posts_published_idx;');

        // Drop the table
        Schema::dropIfExists('posts');
    }
};