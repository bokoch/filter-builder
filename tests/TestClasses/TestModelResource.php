<?php

namespace Mykolab\FilterBuilder\Tests\TestClasses;

use Illuminate\Http\Resources\Json\JsonResource;

class TestModelResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'status' => $this->status,
        ];
    }
}
