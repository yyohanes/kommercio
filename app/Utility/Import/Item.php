<?php

namespace Kommercio\Utility\Import;

use Illuminate\Database\Eloquent\Model;
use Maatwebsite\Excel\Facades\Excel;

class Item extends Model
{
    const STATUS_PENDING = 'pending';
    const STATUS_SUCCESS = 'success';
    const STATUS_ERROR = 'error';
    const STATUS_SKIPPED = 'skipped';

    protected $table = 'import_items';
    protected $fillable = ['name', 'status', 'notes'];

    //Methods
    public function process($row, \Closure $closure)
    {
        $fullFilePath = storage_path('tmp').'/'.$this->batch->name;

        Excel::load($fullFilePath, function($reader) use ($row, $closure){
            // Getting all results
            $results = $reader->get();

            $process = $closure($results->get($row));

            if(is_string($process)){
                $this->update([
                    'status' => self::STATUS_ERROR,
                    'notes' => $process
                ]);
            }elseif(is_array($process)){
                $this->update([
                    'status' => isset($process['status'])?$process['status']:self::STATUS_ERROR,
                    'notes' => isset($process['notes'])?$process['notes']:null,
                ]);
            }else{
                $this->update([
                    'status' => self::STATUS_SUCCESS,
                ]);
            }
        });

        return $this;
    }

    //Relations
    public function batch()
    {
        return $this->belongsTo('Kommercio\Utility\Import\Batch', 'import_batch_id');
    }
}
