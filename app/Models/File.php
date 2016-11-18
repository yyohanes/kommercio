<?php

namespace Kommercio\Models;

use Illuminate\Database\Eloquent\Model;
use Intervention\Image\ImageManagerStatic;
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

    public function saveFile(UploadedFile $uploadedFile, $temporary=true, $uploadPath='default', $resizeTo = false)
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

        if($resizeTo){
            $img = ImageManagerStatic::make($uploadedFile->getRealPath())->widen($resizeTo, function ($constraint) {
                $constraint->upsize();
            });

            $tempSavedPath = storage_path('tmp/tmp_'.$finalFileName);
            $realPath = $tempSavedPath;

            $img->save($tempSavedPath, 90);
        }else{
            $realPath = $uploadedFile->getRealPath();
        }

        Storage::put($uploadPath.$finalFileName, file_get_contents($realPath));

        //Delete after resized image is moved
        if($resizeTo){
            unlink($tempSavedPath);
        }

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
        //temporarily only check if temp
        return !$this->temp;
    }

    //Accessors
    public function getPathAttribute()
    {
        return $this->folder.$this->filename;
    }
}
