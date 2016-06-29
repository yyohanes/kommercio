<?php

namespace Kommercio\Models\CMS;

use Dimsav\Translatable\Translatable;
use Illuminate\Database\Eloquent\Model;
use Kommercio\Traits\Model\ToggleDate;

class Page extends Model
{
    use Translatable, ToggleDate;

    protected $casts = [
        'active' => 'boolean',
    ];
    protected $toggleFields = ['active'];
}
