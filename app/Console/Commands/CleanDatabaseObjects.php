<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class CleanDatabaseObjects extends Command
{
    protected $signature = 'db:clean-objects';
    protected $description = 'Drop all database objects and reinstall only original ones';

    public function handle()
    {
        if (!$this->confirm('⚠️  This will DROP all procedures, triggers, functions, and views. Continue?')) {
            $this->info('Cancelled.');
            return 0;
        }

        $this->info('🗑️  Dropping all database objects...');
        $this->newLine();

        // Drop all procedures
        $this->dropRoutines('PROCEDURE');

        // Drop all functions
        $this->dropRoutines('FUNCTION');

        // Drop all views
        $this->dropViews();

        // Drop all triggers
        $this->dropTriggers();

        $this->newLine();
        $this->info('✅ All database objects dropped!');
        $this->newLine();

        // Reinstall original ones
        $this->info('📦 Reinstalling ORIGINAL database objects...');
        $this->newLine();

        $this->installOriginals();

        $this->newLine();
        $this->info('🎉 Done! Only original database objects are now installed.');
        $this->newLine();

        // Verify
        $this->call('db:check-objects');

        return 0;
    }

    private function dropRoutines($type)
    {
        $routines = DB::select("SELECT ROUTINE_NAME FROM information_schema.ROUTINES WHERE ROUTINE_SCHEMA = DATABASE() AND ROUTINE_TYPE = ?", [$type]);
        
        foreach ($routines as $routine) {
            DB::statement("DROP {$type} IF EXISTS `{$routine->ROUTINE_NAME}`");
            $this->line("  ✓ Dropped {$type}: {$routine->ROUTINE_NAME}");
        }
    }

    private function dropViews()
    {
        $views = DB::select("SELECT TABLE_NAME FROM information_schema.VIEWS WHERE TABLE_SCHEMA = DATABASE()");
        
        foreach ($views as $view) {
            DB::statement("DROP VIEW IF EXISTS `{$view->TABLE_NAME}`");
            $this->line("  ✓ Dropped VIEW: {$view->TABLE_NAME}");
        }
    }

    private function dropTriggers()
    {
        $triggers = DB::select("SELECT TRIGGER_NAME FROM information_schema.TRIGGERS WHERE TRIGGER_SCHEMA = DATABASE()");
        
        foreach ($triggers as $trigger) {
            DB::statement("DROP TRIGGER IF EXISTS `{$trigger->TRIGGER_NAME}`");
            $this->line("  ✓ Dropped TRIGGER: {$trigger->TRIGGER_NAME}");
        }
    }

    private function installOriginals()
    {
        // Install procedures.sql
        $this->installFile('procedures.sql', 'Procedures');

        // Install triggers.sql
        $this->installFile('triggers.sql', 'Triggers');

        // Install functions_views.sql
        $this->installFile('functions_views.sql', 'Functions & Views');
    }

    private function installFile($filename, $label)
    {
        $sqlFile = database_path("sql/{$filename}");
        
        if (!file_exists($sqlFile)) {
            $this->error("⚠️  File {$filename} not found");
            return false;
        }

        try {
            $this->line("Installing {$label}...");
            
            $sql = file_get_contents($sqlFile);
            
            // Remove DELIMITER statements and split by $$
            $sql = str_replace(['DELIMITER $$', 'DELIMITER ;'], '', $sql);
            
            // Split by $$ and filter empty statements
            $statements = array_filter(
                array_map('trim', explode('$$', $sql)),
                fn($stmt) => !empty($stmt) && strlen($stmt) > 10
            );
            
            // Execute each statement
            foreach ($statements as $statement) {
                if (!empty(trim($statement))) {
                    DB::unprepared($statement);
                }
            }
            
            $this->info("✅ {$label} installed");
            return true;
        } catch (\Exception $e) {
            $this->error("Error installing {$filename}: " . $e->getMessage());
            return false;
        }
    }
}
