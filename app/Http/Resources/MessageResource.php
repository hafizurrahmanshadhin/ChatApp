<?php
namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class MessageResource extends JsonResource {
    public function toArray($request) {
        return [
            'id'         => $this->id,
            'message'    => $this->message,
            'sender'     => new UserResource($this->sender),
            'receiver'   => new UserResource($this->receiver),
            'created_at' => $this->created_at,
        ];
    }
}
