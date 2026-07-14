<?php

namespace Tests\Concerns;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

trait RefreshPostgresDatabase
{
    private static bool $postgresSchemaReady = false;

    private static bool $postgresSeeded = false;

    protected function setUpTheDatabase(): void
    {
        if (! self::$postgresSchemaReady) {
            $this->runPostgresFreshMigrations();
            self::$postgresSchemaReady = true;
        }
    }

    protected function runPostgresFreshMigrations(): void
    {
        DB::statement('DROP SCHEMA IF EXISTS public CASCADE');
        DB::statement('CREATE SCHEMA public');
        DB::statement('GRANT ALL ON SCHEMA public TO public');

        $migrator = $this->app->make('migrator');
        $migrator->getRepository()->createRepository();

        foreach ($migrator->getMigrationFiles(database_path('migrations')) as $path) {
            $migrator->run([$path]);
        }
    }

    protected function runDatabaseSeeder(Seeder $seeder): void
    {
        if (self::$postgresSeeded) {
            return;
        }

        $seeder->setContainer($this->app);
        $seeder->__invoke();

        self::$postgresSeeded = true;
    }
}
