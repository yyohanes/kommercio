<?php

namespace Kommercio\Models\Blocks;

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

    protected $guarded = [];
    public $translatedAttributes = ['name', 'body'];
    protected $toggleFields = ['active'];

    public function render()
    {
        $view_name = ProjectHelper::findViewTemplate(['frontend.block.view_'.$this->id, 'frontend.block.view']);

        return view($view_name, [
            'block' => $this,
        ]);
    }
}
