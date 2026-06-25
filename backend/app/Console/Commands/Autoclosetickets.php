<?php

namespace App\Console\Commands;

use App\Services\TicketService;
use Illuminate\Console\Command;

class AutoCloseTickets extends Command
{
    protected $signature   = 'tickets:auto-close';
    protected $description = 'Ferme automatiquement les tickets inactifs depuis 7 jours.';

    /**
     * @var TicketService
     */
    private $ticketService;

    public function __construct(TicketService $ticketService)
    {
        parent::__construct();
        $this->ticketService = $ticketService;
    }

    public function handle(): int
    {
        $this->info('Recherche des tickets inactifs...');

        $count = $this->ticketService->autoCloseInactiveTickets();

        if ($count === 0) {
            $this->line('Aucun ticket à fermer automatiquement.');
        } else {
            $this->info("{$count} ticket(s) fermé(s) automatiquement.");
        }

        return Command::SUCCESS;
    }
}
