<?php

namespace Kommercio\Models\CMS;

use Illuminate\Database\Eloquent\Model;
use Kommercio\Traits\Model\HasDataColumn;

class MenuItemTranslation extends Model
{
    use HasDataColumn;

    public $fillable = [
        'name',
        'url',
        'data',
        'locale',
    ];

    public $timestamps = FALSE;
}
