<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class BaseJsonResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->resource->id ?? null,
            'type' => class_basename($this->resource),
            'attributes' => parent::toArray($request),
        ];
    }
}
