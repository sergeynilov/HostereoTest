<?php

namespace App\Http\Resources;

use DateConv;
use Illuminate\Http\Resources\Json\JsonResource;
use App\Models\PostTag as PostTagModel;

class PostTagResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param \Illuminate\Http\Request $request
     *
     * @return array
     */
    public function toArray($request)
    {
        return [
            'id'      => $this->id,
            'post_id' => $this->post_id,
            'post'    => new PostResource($this->whenLoaded('post')),
            'tag_id'  => $this->tag_id,
            'tag'     => new TagResource($this->whenLoaded('tag')),
        ];
    }

}
