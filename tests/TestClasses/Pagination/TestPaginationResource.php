<?php

namespace Mykolab\FilterBuilder\Tests\TestClasses\Pagination;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class TestPaginationResource extends JsonResource
{
    /**
     * @param  Request  $request
     * @return array
     */
    public function toArray($request): array
    {
        return [
            'foo' => 'bar',
        ];
    }
}
