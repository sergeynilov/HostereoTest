<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PostTag extends Model
{
    protected $table       = 'post_tags';
    protected $primaryKey  = 'id';
    public $timestamps     = false;
    use HasFactory;

    protected $fillable = ['post_id', 'tag_id'];

    public function post(): BelongsTo
    {
        return $this->belongsTo(Post::class);
    }

    public function tag(): BelongsTo
    {
        return $this->belongsTo(Tag::class);
    }

    public function scopeGetByPostId($query, ?int $filterPostId= null)
    {
        if (!empty($filterPostId)) {
            $query->where($this->table . '.post_id', $filterPostId);
        }
        return $query;
    }

    public function scopeGetByTagId($query, ?int $filterTagId= null)
    {
        if (!empty($filterTagId)) {
            $query->where($this->table . '.tag_id', $filterTagId);
        }
        return $query;
    }

}
