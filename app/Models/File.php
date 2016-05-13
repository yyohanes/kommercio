<?php

namespace Kommercio\Models;

use Illuminate\Database\Eloquent\Model;
use Kommercio\Models\Interfaces\AuthorSignatureInterface;
use Kommercio\Traits\Model\AuthorSignature;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Illuminate\Support\Facades\Storage;

class File extends Model implements AuthorSignatureInterface
{
    const MAXIMUM_SIZE = 8000;
    use AuthorSignature;

    protected $attachable_table;
    protected $guarded = [];
    protected $casts = [
        'temp' => 'boolean'
    ];

    public function saveFile(UploadedFile $uploadedFile, $temporary=true, $uploadPath='default')
    {
        $storage = config('filesystems.default');
        $filename = pathinfo($uploadedFile->getClientOriginalName(), PATHINFO_FILENAME);
        if(strlen($filename) > 200){
            $filename = substr($filename, 0, 200);
        }

        $extension = $uploadedFile->getClientOriginalExtension();
        $uploadPath = rtrim($uploadPath, '/') . '/';

        $finalFileName = $filename.'.'.$extension;
        $duplicateCount = 0;
        while(Storage::exists($uploadPath.$finalFileName)){
            $duplicateCount += 1;
            $finalFileName = $filename.($duplicateCount+1).'.'.$extension;
        }
        Storage::put($uploadPath.$finalFileName, file_get_contents($uploadedFile->getRealPath()));

        $this->fill([
            'storage' => $storage,
            'folder' => $uploadPath,
            'filename' => $finalFileName,
            'filesize' => $uploadedFile->getClientSize(),
            'mime' => $uploadedFile->getClientMimeType(),
            'temp' => $temporary
        ]);

        return $this->save();
    }

    public function markPermanent($save=true)
    {
        $this->temp = false;
        if($save){
            $this->save();
        }
    }

    public function isUsed()
    {
        //temporarily not used
    }

    //Accessors
    public function getPathAttribute()
    {
        return $this->folder.$this->filename;
    }
}
