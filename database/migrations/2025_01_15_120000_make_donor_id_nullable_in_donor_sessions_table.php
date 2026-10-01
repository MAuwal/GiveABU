<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Check database driver - SQLite requires different approach
        $driver = DB::connection()->getDriverName();
        
        if ($driver === 'sqlite') {
            // SQLite doesn't support ALTER COLUMN directly
            // We need to recreate the table with nullable donor_id
            DB::statement('PRAGMA foreign_keys=off;');
            
            // Get all columns from existing table
            $columns = DB::select("PRAGMA table_info(donor_sessions)");
            $columnNames = array_column($columns, 'name');
            
            // Create new table with nullable donor_id
            // Build column list based on existing structure
            DB::statement("
                CREATE TABLE donor_sessions_new (
                    id INTEGER PRIMARY KEY AUTOINCREMENT,
                    username VARCHAR(255) NOT NULL UNIQUE,
                    password VARCHAR(255),
                    donor_id INTEGER NULL,
                    device_session_id INTEGER NULL,
                    auth_provider VARCHAR(50) DEFAULT 'email',
                    google_id VARCHAR(255) NULL,
                    google_email VARCHAR(255) NULL,
                    google_name VARCHAR(255) NULL,
                    google_picture TEXT NULL,
                    created_at TIMESTAMP NULL,
                    updated_at TIMESTAMP NULL
                )
            ");
            
            // Copy data from old table (only columns that exist in both)
            // Use dynamic column selection to handle missing columns gracefully
            $existingColumns = array_column($columns, 'name');
            $columnsToCopy = array_intersect(
                ['id', 'username', 'password', 'donor_id', 'device_session_id', 'auth_provider', 'google_id', 'google_email', 'google_name', 'google_picture', 'created_at', 'updated_at'],
                $existingColumns
            );
            
            $columnsList = implode(', ', $columnsToCopy);
            DB::statement("
                INSERT INTO donor_sessions_new ({$columnsList})
                SELECT {$columnsList}
                FROM donor_sessions
            ");
            
            // Drop old table
            DB::statement('DROP TABLE donor_sessions;');
            
            // Rename new table
            DB::statement('ALTER TABLE donor_sessions_new RENAME TO donor_sessions;');
            
            // Recreate indexes and foreign keys
            DB::statement('CREATE UNIQUE INDEX IF NOT EXISTS donor_sessions_username_unique ON donor_sessions(username);');
            DB::statement('CREATE INDEX IF NOT EXISTS donor_sessions_google_id_index ON donor_sessions(google_id);');
            DB::statement('CREATE INDEX IF NOT EXISTS donor_sessions_google_email_index ON donor_sessions(google_email);');
            if (in_array('device_session_id', $columnNames)) {
                DB::statement('CREATE INDEX IF NOT EXISTS donor_sessions_device_session_id_index ON donor_sessions(device_session_id);');
            }
            
            DB::statement('PRAGMA foreign_keys=on;');
        } else {
            // For MySQL, PostgreSQL, etc. - use standard ALTER TABLE
            // First, drop the foreign key constraint
            Schema::table('donor_sessions', function (Blueprint $table) {
                $table->dropForeign(['donor_id']);
            });
            
            // Modify the column to be nullable (requires doctrine/dbal package)
            Schema::table('donor_sessions', function (Blueprint $table) {
                $table->unsignedBigInteger('donor_id')->nullable()->change();
            });
            
            // Re-add the foreign key constraint with onDelete('set null')
            Schema::table('donor_sessions', function (Blueprint $table) {
                $table->foreign('donor_id')
                      ->references('id')
                      ->on('donors')
                      ->onDelete('set null');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('donor_sessions', function (Blueprint $table) {
            // Drop the foreign key
            $table->dropForeign(['donor_id']);
            
            // Make it required again (this will fail if there are null values)
            $table->unsignedBigInteger('donor_id')->nullable(false)->change();
            
            // Re-add foreign key with cascade
            $table->foreign('donor_id')
                  ->references('id')
                  ->on('donors')
                  ->onDelete('cascade');
        });
    }
};

