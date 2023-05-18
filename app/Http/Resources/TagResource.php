<?php

namespace App\Http\Resources;

use App\Library\Facades\DateConv;
use Illuminate\Http\Resources\Json\JsonResource;

class TagResource extends JsonResource
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
            'id'                   => $this->id,
            'name'                 => $this->name,
            'created_at'           => $this->created_at,
            'created_at_formatted' => DateConv::getFormattedDateTime($this->created_at),
            'updated_at'           => $this->updated_at,
            'updated_at_formatted' => DateConv::getFormattedDateTime($this->updated_at),
            'deleted_at'           => $this->deleted_at,
            'deleted_at_formatted' => DateConv::getFormattedDateTime($this->updated_at),
        ];
    }

}
