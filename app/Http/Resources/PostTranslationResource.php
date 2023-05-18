<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;
use App\Models\PostTranslation as PostTranslationModel;

class PostTranslationResource extends JsonResource
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
            'id'          => $this->id,
            'post_id'     => $this->post_id,
            'post'        => new PostResource($this->whenLoaded('post')),
            'language_id' => $this->language_id,
            'language'    => new LanguageResource($this->whenLoaded('language')),
            'title'       => $this->title,
            'description' => $this->description,
            'content'     => $this->content,
        ];
    }

}
