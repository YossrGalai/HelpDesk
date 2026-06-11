<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Ticket\CreateTicketRequest;
use App\Http\Requests\Ticket\UpdateTicketRequest;
use App\Http\Resources\Ticket\TicketResource;
use App\Http\Requests\Ticket\AssignTicketRequest;
use App\Http\Requests\Ticket\SetPriorityRequest;
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
     */
    public function index(Request $request): JsonResponse
    {
        $paginator = $this->ticketService->list(
            $request->user(),
            $request->only(['status', 'priority', 'assigned_to'])
        );

        return response()->json([
            'data'  => TicketResource::collection($paginator->items()),
            'meta'  => [
                'current_page' => $paginator->currentPage(),
                'last_page'    => $paginator->lastPage(),
                'per_page'     => $paginator->perPage(),
                'total'        => $paginator->total(),
                'from'         => $paginator->firstItem(),
                'to'           => $paginator->lastItem(),
            ],
            'links' => [
                'first' => $paginator->url(1),
                'last'  => $paginator->url($paginator->lastPage()),
                'prev'  => $paginator->previousPageUrl(),
                'next'  => $paginator->nextPageUrl(),
            ],
        ]);
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

    /**
     * PATCH /api/tickets/{ticket}/assign
     * Admin uniquement — protégé par middleware 'role:admin' dans les routes.
     */
    public function assign(AssignTicketRequest $request, Ticket $ticket): JsonResponse
    {
        $updated = $this->ticketService->assign(
            $request->user(),
            $ticket,
            $request->validated()['assigned_to']
        );

        return (new TicketResource($updated))
            ->response()
            ->setStatusCode(200);
    }

    /**
     * PATCH /api/tickets/{ticket}/priority
     * Admin uniquement — protégé par middleware 'role:admin' dans les routes.
     */
    public function setPriority(SetPriorityRequest $request, Ticket $ticket): JsonResponse
    {
        $updated = $this->ticketService->setPriority(
            $request->user(),
            $ticket,
            $request->validated()['priority']
        );

        return (new TicketResource($updated))
            ->response()
            ->setStatusCode(200);
    }
}
