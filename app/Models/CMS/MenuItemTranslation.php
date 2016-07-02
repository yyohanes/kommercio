<?php

namespace Kommercio\Models\CMS;

use Cviebrock\EloquentSluggable\SluggableInterface;
use Cviebrock\EloquentSluggable\SluggableTrait;
use Illuminate\Database\Eloquent\Model;
use Kommercio\Traits\Model\HasDataColumn;

class MenuItemTranslation extends Model
{
    use HasDataColumn;

    public $timestamps = FALSE;
}
