<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;
use App\Models\Post as PostModel;
use DateConv;
use App;

class PostResource extends JsonResource
{

    public function toArray($request)
    {
        return [
            'id'                   => $this->id,
            'created_at'           => $this->created_at,
            'created_at_formatted' => DateConv::getFormattedDateTime($this->created_at),
            'updated_at'           => $this->updated_at,
            'updated_at_formatted' => DateConv::getFormattedDateTime($this->updated_at),
            'deleted_at'           => $this->deleted_at,
            'deleted_at_formatted' => DateConv::getFormattedDateTime($this->updated_at),

            'postTranslations' => PostTranslationResource::collection($this->whenLoaded('postTranslations')),
            'postTags'         => PostTagResource::collection($this->whenLoaded('postTags')),
            'tags'             => TagResource::collection($this->whenLoaded('tags')),
        ];
    }

}
