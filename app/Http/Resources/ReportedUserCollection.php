<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\ResourceCollection;

class ReportedUserCollection extends ResourceCollection
{
    public function toArray($request)
    {
        return [
            'reported_user' => ReportedUserResource::collection($this->collection),
            'pagination' => [
                'total'        => $this->total(),
                'current_page' => $this->currentPage(),
                'last_page'    => $this->lastPage(),
                'per_page'     => $this->perPage(),
            ],
        ];
    }
}
