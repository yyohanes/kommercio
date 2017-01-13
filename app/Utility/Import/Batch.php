<?php

namespace Kommercio\Utility\Import;

use Illuminate\Database\Eloquent\Model;

class Batch extends Model
{
    protected $table = 'import_batches';
    protected $fillable = ['name'];

    //Relations
    public function items()
    {
        return $this->hasMany('Kommercio\Utility\Import\Item', 'import_items')->orderBy('id', 'ASC');
    }
}
