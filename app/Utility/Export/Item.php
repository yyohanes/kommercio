<?php

namespace Kommercio\Utility\Export;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\File;
use Kommercio\Traits\Model\HasDataColumn;
use Maatwebsite\Excel\Facades\Excel;

class Item extends Model
{
    use HasDataColumn;

    const STATUS_PENDING = 'pending';
    const STATUS_SUCCESS = 'success';
    const STATUS_ERROR = 'error';
    const STATUS_SKIPPED = 'skipped';

    protected $table = 'export_items';
    public $fillable = ['name', 'status', 'notes'];

    //Methods
    public function getFilename()
    {
        return $this->name.'.'.$this->batch->extension;
    }

    public function process($rowNumber = 0, \Closure $closure)
    {
        $fullFilePath = $this->batch->getStoragePath().'/'.$this->getFilename();

        if(!File::exists($this->batch->getStoragePath())){
            File::makeDirectory($this->batch->getStoragePath(), 0755, true);
        }

        $data = $closure($this->getData('rows', []), $rowNumber);

        if(!isset($data['rows'])){
            abort(422, 'Closure doesn\'t return array with key "rows"');
        }

        if(!is_array($data['rows'])){
            abort(422, 'Rows is not an array.');
        }

        foreach($data['rows'] as $row){
            $this->generateCsv($row, $fullFilePath);
        }

        return $this;
    }

    protected function generateCsv(array $datum, $filename,  $delimiter = ',', $enclosure = '"') {
        $handle = fopen($filename, 'a+');
        fputcsv($handle, $datum, $delimiter, $enclosure);

        rewind($handle);
        $contents = '';
        while (!feof($handle)) {
            $contents .= fread($handle, filesize($filename));
        }
        fclose($handle);
        return $contents;
    }

    //Relations
    public function batch()
    {
        return $this->belongsTo('Kommercio\Utility\Export\Batch', 'export_batch_id');
    }
}
