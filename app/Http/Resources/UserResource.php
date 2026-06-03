<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;

class UserResource extends BaseJsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->resource->id,
            'type' => 'User',
            'attributes' => [
                'name' => $this->resource->name,
                'email' => $this->resource->email,
                'role' => $this->resource->role->value,
                'created_at' => $this->resource->created_at?->toISOString(),
            ],
        ];
    }
}
