<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Validation\Rule;

class Tag extends Model
{
    public $timestamps = false;
    protected $table = 'tags';
    protected $primaryKey = 'id';
    protected $fillable = ['name'];
    protected $casts = [];
    use HasFactory;
    use SoftDeletes;

    public function scopeGetById($query, $id)
    {
        return $query->where($this->table . '.id', $id);
    }

    public function postTags(): HasMany
    {
        return $this->hasMany(PostTag::class);
    }

    public function scopeGetBySearch($query, ?string $search = null, bool $partial = false)
    {
        if (empty($search)) {
            return $query;
        }

        return $query->where(
            $this->table . '.name',
            (! $partial ? '=' : 'like'),
            ($partial ? '%' : '') . $search . ($partial ? '%' : '')
        );
    }

    public static function getValidationRulesArray($tagId = null): array
    {
        $validationRulesArray = [
            'name'             => [
                'required',
                'string',
                'max:100',
                Rule::unique((new Tag)->getTable())->ignore($tagId),
            ],

        ];

        return $validationRulesArray;
    }

}
