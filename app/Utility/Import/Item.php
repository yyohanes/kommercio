<?php

namespace Kommercio\Utility\Import;

use Illuminate\Database\Eloquent\Model;

class Item extends Model
{
    const STATUS_PENDING = 'pending';
    const STATUS_SUCCESS = 'success';
    const STATUS_ERROR = 'error';

    protected $table = 'import_items';
    protected $fillable = ['name', 'status', 'notes'];

    //Relations
    public function batch()
    {
        return $this->belongsTo('Kommercio\Utility\Import\Batch', 'import_batches');
    }
}
