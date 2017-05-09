<?php

namespace Kommercio\Models\CMS;

use Dimsav\Translatable\Translatable;
use Illuminate\Database\Eloquent\Model;
use Kommercio\Facades\ProjectHelper;
use Kommercio\Traits\Model\ToggleDate;

class Block extends Model
{
    use Translatable, ToggleDate{
        Translatable::setAttribute as translateableSetAttribute;
        ToggleDate::setAttribute insteadof Translatable;
    }

    const TYPE_STATIC = 'static';

    protected $fillable = ['name', 'body', 'machine_name', 'type', 'active'];
    public $translatedAttributes = ['name', 'body'];
    protected $toggleFields = ['active'];

    //Scope
    public function scopeActive($query)
    {
        $query->where('active', true);
    }

    public function render()
    {
        $view_name = ProjectHelper::findViewTemplate(['frontend.block.view_'.$this->machine_name, 'frontend.block.view']);

        return view($view_name, [
            'block' => $this,
        ]);
    }
}
