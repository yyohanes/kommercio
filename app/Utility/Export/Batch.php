<?php

namespace Kommercio\Utility\Export;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\File;

class Batch extends Model
{
    public $extension = 'csv';
    protected $table = 'export_batches';
    protected $fillable = ['name'];

    //Methods
    public function getBatchUniqueName()
    {
        return 'batch_'.$this->id;
    }

    public function getStoragePath()
    {
        return storage_path('tmp').'/export/'.$this->getBatchUniqueName();
    }

    public function clean()
    {
        foreach($this->items as $item){
            File::delete($this->getStoragePath().'/'.$item->name.'.'.$this->extension);
        }
    }

    public function getFilename()
    {
        return $this->name.'.'.$this->extension;
    }

    public function combineFiles()
    {
        if($this->items->count() > 0){
            $fileName = $this->getFilename();
            File::put($this->getStoragePath().'/'.$fileName, '');

            foreach($this->items as $item){
                $savedFile = file_put_contents($this->getStoragePath().'/'.$fileName, file_get_contents($this->getStoragePath().'/'.$item->getFilename()), FILE_APPEND);
            }
        }
    }

    public function process($row, \Closure $closure)
    {
        $item = $this->items->get($row);

        try{
            $item->process($row, $closure);

            $item->update([
                'status' => Item::STATUS_SUCCESS
            ]);
        }catch(\Exception $e){
            $item->update([
                'status' => Item::STATUS_ERROR,
                'notes' => $e->getMessage()
            ]);
        }

        return $item;
    }

    public function hasRow($rowNumber)
    {
        return $this->items->has($rowNumber);
    }

    //Relations
    public function items()
    {
        return $this->hasMany('Kommercio\Utility\Export\Item', 'export_batch_id')->orderBy('id', 'ASC');
    }

    //Statics
    public static function init(Collection $rows, $name = null, $numberPerBatch = 500)
    {
        $results = $rows->chunk($numberPerBatch);

        $timestamp = str_replace([' ', ':'], '-', Carbon::now()->toDateTimeString());
        $batchName = 'export-'.$timestamp. ($name?'-' .$name:'');

        $exportBatch = self::create([
            'name' => $batchName
        ]);

        foreach($results as $idx=>$result) {
            $batchItem = new Item([
                'name' => $name.'-'.($idx+1),
                'status' => Item::STATUS_PENDING,
            ]);

            // If $rows is collection of model, we only save the IDs for later loading
            if ($rows instanceof \Illuminate\Database\Eloquent\Collection) {
                $batchItem->saveData(['rows' => $result->pluck('id')->all()]);
            } else {
                $batchItem->saveData(['rows' => $result->all()]);
            }

            $batchItem->batch()->associate($exportBatch);
            $batchItem->save();
        }

        return $exportBatch;
    }
}
