<?php

namespace Kommercio\Models\CMS;

use Illuminate\Database\Eloquent\Model;

class Menu extends Model
{
    protected $fillable = ['name', 'description'];

    //Relations
    public function menuItems()
    {
        return $this->hasMany('Kommercio\Models\CMS\MenuItem')->orderBy('sort_order', 'ASC');
    }
}
