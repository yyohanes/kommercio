<?php

namespace Kommercio\Utility\Import;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\File;
use Maatwebsite\Excel\Facades\Excel;

class Batch extends Model
{
    protected $table = 'import_batches';
    protected $fillable = ['name'];

    //Methods
    public function clean()
    {
        File::delete(storage_path('tmp').'/'.$this->name);
    }

    public function process($row, \Closure $closure)
    {
        return $this->items->get($row)->process($row, $closure);
    }

    public function hasRow($rowNumber)
    {
        return $this->items->has($rowNumber);
    }

    //Relations
    public function items()
    {
        return $this->hasMany('Kommercio\Utility\Import\Item', 'import_batch_id')->orderBy('id', 'ASC');
    }

    //Statics
    public static function init(UploadedFile $file)
    {
        $timestamp = str_replace([' ', ':'], '-', Carbon::now()->toDateTimeString());
        $name = 'import-'.$timestamp. '-' .$file->getClientOriginalName();

        $importBatch = self::create([
            'name' => $name
        ]);

        $file->move(storage_path('tmp'), $name);

        $fullFilePath = storage_path('tmp').'/'.$name;

        Excel::load($fullFilePath, function($reader) use ($importBatch){
            // Getting all results
            $results = $reader->get();

            foreach($results as $idx=>$result) {
                $batchItem = new Item([
                    'name' => 'Row '.($idx+1),
                    'status' => Item::STATUS_PENDING,
                ]);

                $batchItem->batch()->associate($importBatch);
                $batchItem->save();
            }
        });

        return $importBatch;
    }
}
