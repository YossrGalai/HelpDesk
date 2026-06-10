<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Ticket\CreateTicketRequest;
use App\Http\Requests\Ticket\UpdateTicketRequest;
use App\Http\Resources\Ticket\TicketCollection;
use App\Http\Resources\Ticket\TicketResource;
use App\Models\Ticket;
use App\Services\TicketService;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class TicketController extends Controller
{
    /**
     * @var TicketService
     */
    private $ticketService;

    public function __construct(TicketService $ticketService)
    {
        $this->ticketService = $ticketService;
    }

    /**
     * GET /api/tickets
     * Liste paginée — filtrable par status et priority.
     *
     * @param  Request  $request
     * @return TicketCollection
     */
    public function index(Request $request): TicketCollection
    {
        $tickets = $this->ticketService->list(
            $request->user(),
            $request->only(['status', 'priority'])
        );

        return new TicketCollection($tickets);
    }

    /**
     * POST /api/tickets
     * Créer un nouveau ticket.
     *
     * @param  CreateTicketRequest  $request
     * @return JsonResponse
     */
    public function store(CreateTicketRequest $request): JsonResponse
    {
        $ticket = $this->ticketService->create(
            $request->user(),
            $request->validated()
        );

        return (new TicketResource($ticket))
            ->response()
            ->setStatusCode(201);
    }

    /**
     * GET /api/tickets/{id}
     * Détail d'un ticket.
     *
     * @param  int  $id
     * @return JsonResponse
     */
    public function show(int $id): JsonResponse
    {
        $ticket = $this->ticketService->find($id);

        return (new TicketResource($ticket))
            ->response()
            ->setStatusCode(200);
    }

    /**
     * PATCH /api/tickets/{ticket}
     * Mise à jour partielle — seul le créateur ou un admin.
     *
     * @param  UpdateTicketRequest  $request
     * @param  Ticket               $ticket
     * @return JsonResponse
     */
    public function update(UpdateTicketRequest $request, Ticket $ticket): JsonResponse
    {
        try {
            $updated = $this->ticketService->update(
                $request->user(),
                $ticket,
                $request->validated()
            );
        } catch (AuthorizationException $e) {
            return response()->json(['message' => $e->getMessage()], 403);
        }

        return (new TicketResource($updated))
            ->response()
            ->setStatusCode(200);
    }

    /**
     * PATCH /api/tickets/{ticket}/close
     * Fermer un ticket — endpoint dédié.
     *
     * @param  Request  $request
     * @param  Ticket   $ticket
     * @return JsonResponse
     */
    public function close(Request $request, Ticket $ticket): JsonResponse
    {
        try {
            $closed = $this->ticketService->close($request->user(), $ticket);
        } catch (AuthorizationException $e) {
            return response()->json(['message' => $e->getMessage()], 403);
        }

        return (new TicketResource($closed))
            ->response()
            ->setStatusCode(200);
    }
}
