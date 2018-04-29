<?php

namespace Kommercio\Models\CMS;

use Illuminate\Database\Eloquent\Model;

class BlockTranslation extends Model
{
    public $fillable = [
        'name',
        'body',
        'locale',
    ];

    public $timestamps = FALSE;
}
