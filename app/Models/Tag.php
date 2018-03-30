<?php

namespace Kommercio\Models;

use Kommercio\Models\Abstracts\SluggableModel;
use Kommercio\Models\Interfaces\AuthorSignatureInterface;
use Kommercio\Traits\Model\AuthorSignature;

class Tag extends SluggableModel implements AuthorSignatureInterface
{
    use AuthorSignature;

    protected $fillable = ['name', 'slug', 'notes'];

    //Relations
    public function taggable()
    {
        return $this->morphTo();
    }
}
