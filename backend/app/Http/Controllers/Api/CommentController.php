<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Comment\CreateCommentRequest;
use App\Http\Resources\Comment\CommentResource;
use App\Http\Resources\Ticket\TicketResource;
use App\Models\Ticket;
use App\Services\CommentService;
use Illuminate\Http\JsonResponse;

class CommentController extends Controller
{
    /**
     * @var CommentService
     */
    private $commentService;

    public function __construct(CommentService $commentService)
    {
        $this->commentService = $commentService;
    }

    /**
     * GET /api/tickets/{ticket}/comments
     *
     * Retourner la timeline des commentaires d'un ticket.
     * Triés du plus ancien au plus récent.
     *
     * @param  Ticket  $ticket
     * @return JsonResponse
     */
    public function index(Ticket $ticket): JsonResponse
    {
        $comments = $this->commentService->list($ticket);

        return response()->json([
            'ticket' => new TicketResource($ticket),
            'data' => CommentResource::collection($comments),
        ]);
    }

    /**
     * POST /api/tickets/{ticket}/comments
     *
     * Ajouter un commentaire à un ticket.
     * Les commentaires ne peuvent pas être modifiés ou supprimés.
     *
     * @param  CreateCommentRequest  $request
     * @param  Ticket                $ticket
     * @return JsonResponse
     */
    public function store(CreateCommentRequest $request, Ticket $ticket): JsonResponse
    {
        $comment = $this->commentService->create(
            $request->user(),
            $ticket,
            $request->validated()['comment']
        );

        return (new CommentResource($comment))
            ->response()
            ->setStatusCode(201);
    }
}
