<?php

namespace App\Http\Resources\Ticket;

use App\Http\Resources\Auth\UserResource;
use Illuminate\Http\Resources\Json\JsonResource;

class TicketResource extends JsonResource
{
    /**
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function toArray($request)
    {
        return [
            'id'          => $this->id,
            'title'       => $this->title,
            'description' => $this->description,
            'status'      => $this->status,
            'priority'    => $this->priority,
            'creator'     => new UserResource($this->whenLoaded('creator')),
            'assignee'    => $this->whenLoaded('assignee', function () {
                return $this->assignee ? new UserResource($this->assignee) : null;
            }),
            'comments_count' => $this->whenLoaded('comments', function () {
                return $this->comments->count();
            }),
            'history' => TicketHistoryResource::collection($this->whenLoaded('histories')),
            'created_at'  => $this->created_at->toDateTimeString(),
            'updated_at'  => $this->updated_at->toDateTimeString(),
        ];
    }
}
