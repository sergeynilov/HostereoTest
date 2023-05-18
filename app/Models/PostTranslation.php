<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Arr;

class PostTranslation extends Model
{
    protected $table = 'post_translations';
    protected $primaryKey = 'id';
    public $timestamps = false;
    use HasFactory;

    protected $fillable = ['post_id', 'language_id', 'title', 'description', 'content'];

    public function post(): BelongsTo
    {
        return $this->belongsTo(Post::class);
    }

    public function language(): BelongsTo
    {
        return $this->belongsTo(Language::class);
    }

    public function scopeGetByPostId($query, ?int $filterPostId = null)
    {
        if ( ! empty($filterPostId)) {
            $query->where($this->table . '.post_id', $filterPostId);
        }

        return $query;
    }

    public function scopeGetByLanguageId($query, ?int $filterLanguageId = null)
    {
        if ( ! empty($filterLanguageId)) {
            $query->where($this->table . '.language_id', $filterLanguageId);
        }

        return $query;
    }

    public function scopeGetBySearch($query, ?string $search = null, bool $partial = false)
    {
        if (empty($search)) {
            return $query;
        }

        return $query->where( $this->table . '.title', (! $partial ? '=' : 'like'), ($partial ? '%' : '') . $search . ($partial ? '%' : '') )
                ->orWhere( $this->table . '.description', (! $partial ? '=' : 'like'), ($partial ? '%' : '') . $search . ($partial ? '%' : '') )
                ->orWhere( $this->table . '.content', (! $partial ? '=' : 'like'), ($partial ? '%' : '') . $search . ($partial ? '%' : '') );
    }

    public static function getValidationRulesArray($postId = null, array $skipFieldsArray = []): array
    {
        $validationRulesArray = [
            'post_id'     => 'required|exists:' . ((new Post)->getTable()) . ',id',
            'language_id' => 'required|exists:' . ((new Language)->getTable()) . ',id',
            'title'       => 'string|required|max:255',
            'description' => 'string|required',
            'content'     => 'string|required',
        ];

        foreach ($skipFieldsArray as $next_field) {
            if ( ! empty($validationRulesArray[$next_field])) {
                $validationRulesArray = Arr::except($validationRulesArray, $next_field);
            }
        }

        return $validationRulesArray;
    }

}
