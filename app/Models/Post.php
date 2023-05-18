<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Post extends Model
{
    use SoftDeletes;
    use HasFactory;

    public $timestamps    = false;
    protected $table      = 'posts';
    protected $primaryKey = 'id';
    protected $fillable   = ['created_at'];
    protected $casts
        = [
            'created_at' => 'datetime:j F, Y g:i:s A',
            'updated_at' => 'datetime:j F, Y g:i:s A',
            'deleted_at' => 'datetime:j F, Y g:i:s A',
        ];

    public function scopeGetById($query, $id)
    {
        return $query->where($this->table . '.id', $id);
    }

    public function postTranslations(): HasMany
    {
        return $this->hasMany(PostTranslation::class);
    }

    public function postTags(): HasMany
    {
        return $this->hasMany(PostTag::class);
    }

}
