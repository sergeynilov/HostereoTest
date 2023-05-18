<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Language extends Model
{
    public $timestamps = false;
    protected $table = 'languages';
    protected $primaryKey = 'id';
    protected $fillable
        = [
            'locale',
            'prefix',
        ];
    protected $casts = [];


    public function postTranslations(): HasMany
    {
        return $this->hasMany(PostTranslation::class);
    }


}
