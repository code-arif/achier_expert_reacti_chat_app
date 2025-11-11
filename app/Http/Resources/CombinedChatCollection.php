<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\ResourceCollection;

class CombinedChatCollection extends ResourceCollection
{
    public function toArray($request)
    {
        // dd($this->total());
        return [
            'chats' => CombinedChatResource::collection($this->collection),
            'pagination' => [
                'total'        => $this->total(),
                'current_page' => $this->currentPage(),
                'last_page'    => $this->lastPage(),
                'per_page'     => $this->perPage(),
            ],
        ];
    }
}
