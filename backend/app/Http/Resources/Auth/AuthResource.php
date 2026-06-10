<?php

namespace App\Http\Resources\Auth;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * $this->resource = ['user' => User, 'token' => string]
 */
class AuthResource extends JsonResource
{
    /**
     * @param  Request  $request
     * @return array
     */
    public function toArray($request)
    {
        return [
            'user'  => new UserResource($this->resource['user']),
            'token' => $this->resource['token'],
        ];
    }
}
