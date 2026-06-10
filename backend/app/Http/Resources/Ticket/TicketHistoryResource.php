<?php

namespace App\Http\Resources\Ticket;

use App\Http\Resources\Auth\UserResource;
use Illuminate\Http\Resources\Json\JsonResource;

class TicketHistoryResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            'id'         => $this->id,
            'field'      => $this->field,
            'old_value'  => $this->old_value,
            'new_value'  => $this->new_value,
            'changed_by' => new UserResource($this->whenLoaded('changedBy')),
            'created_at' => $this->created_at->toDateTimeString(),
        ];
    }
}
