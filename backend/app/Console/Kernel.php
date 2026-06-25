<?php

// ─────────────────────────────────────────────────────────────────────────────
// INSTRUCTIONS : app/Console/Kernel.php
// ─────────────────────────────────────────────────────────────────────────────
//
// 1) Ajouter AutoCloseTickets dans le tableau $commands :
//
//    protected $commands = [
//        \App\Console\Commands\AutoCloseTickets::class,
//    ];
//
// 2) Ajouter la ligne dans la méthode schedule() :
//
//    protected function schedule(Schedule $schedule): void
//    {
//        $schedule->command('tickets:auto-close')->dailyAt('00:00');
//    }
//
// 3) Activer le cron Laravel sur le serveur (une seule ligne dans crontab) :
//    crontab -e  →  ajouter :
//    * * * * * cd /chemin/vers/projet && php artisan schedule:run >> /dev/null 2>&1
//
// ─────────────────────────────────────────────────────────────────────────────
// EXEMPLE COMPLET du Kernel.php (Laravel 9 / PHP 7.4)
// ─────────────────────────────────────────────────────────────────────────────

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    protected $commands = [
        \App\Console\Commands\AutoCloseTickets::class,
    ];

    protected function schedule(Schedule $schedule): void
    {
        $schedule->command('tickets:auto-close')->dailyAt('00:00');
    }

    protected function commands(): void
    {
        $this->load(__DIR__ . '/Commands');
        require base_path('routes/console.php');
    }
}
