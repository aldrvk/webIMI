<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class CheckDatabaseObjects extends Command
{
    protected $signature = 'db:check-objects';
    protected $description = 'Check how many database objects are installed';

    public function handle()
    {
        $this->info('📊 Checking Database Objects...');
        $this->newLine();

        // Check Procedures
        $procedures = DB::select("SELECT COUNT(*) as total FROM information_schema.ROUTINES WHERE ROUTINE_SCHEMA = DATABASE() AND ROUTINE_TYPE = 'PROCEDURE'")[0]->total;
        $this->line("✅ Procedures: {$procedures}");

        // Check Triggers
        $triggers = DB::select("SELECT COUNT(*) as total FROM information_schema.TRIGGERS WHERE TRIGGER_SCHEMA = DATABASE()")[0]->total;
        $this->line("✅ Triggers: {$triggers}");

        // Check Functions
        $functions = DB::select("SELECT COUNT(*) as total FROM information_schema.ROUTINES WHERE ROUTINE_SCHEMA = DATABASE() AND ROUTINE_TYPE = 'FUNCTION'")[0]->total;
        $this->line("✅ Functions: {$functions}");

        // Check Views
        $views = DB::select("SELECT COUNT(*) as total FROM information_schema.VIEWS WHERE TABLE_SCHEMA = DATABASE()")[0]->total;
        $this->line("✅ Views: {$views}");

        $this->newLine();
        $total = $procedures + $triggers + $functions + $views;
        $this->info("TOTAL: {$total} database objects");

        // Detail list
        $this->newLine();
        $this->info('📋 Details:');
        
        // List Procedures
        if ($procedures > 0) {
            $this->line('PROCEDURES:');
            $procs = DB::select("SELECT ROUTINE_NAME FROM information_schema.ROUTINES WHERE ROUTINE_SCHEMA = DATABASE() AND ROUTINE_TYPE = 'PROCEDURE' ORDER BY ROUTINE_NAME");
            foreach ($procs as $proc) {
                $this->line("  - {$proc->ROUTINE_NAME}");
            }
        }

        // List Functions
        if ($functions > 0) {
            $this->newLine();
            $this->line('FUNCTIONS:');
            $funcs = DB::select("SELECT ROUTINE_NAME FROM information_schema.ROUTINES WHERE ROUTINE_SCHEMA = DATABASE() AND ROUTINE_TYPE = 'FUNCTION' ORDER BY ROUTINE_NAME");
            foreach ($funcs as $func) {
                $this->line("  - {$func->ROUTINE_NAME}");
            }
        }

        // List Views
        if ($views > 0) {
            $this->newLine();
            $this->line('VIEWS:');
            $vws = DB::select("SELECT TABLE_NAME FROM information_schema.VIEWS WHERE TABLE_SCHEMA = DATABASE() ORDER BY TABLE_NAME");
            foreach ($vws as $view) {
                $this->line("  - {$view->TABLE_NAME}");
            }
        }

        // List Triggers
        if ($triggers > 0) {
            $this->newLine();
            $this->line('TRIGGERS:');
            $trgs = DB::select("SELECT TRIGGER_NAME FROM information_schema.TRIGGERS WHERE TRIGGER_SCHEMA = DATABASE() ORDER BY TRIGGER_NAME");
            foreach ($trgs as $trigger) {
                $this->line("  - {$trigger->TRIGGER_NAME}");
            }
        }
        
        return 0;
    }
}
