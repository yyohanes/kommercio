<?php

namespace Kommercio\Models;

use GuzzleHttp\Client;
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
        while(Storage::disk($storage)->exists($uploadPath.$finalFileName)){
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

        Storage::disk($storage)->put($uploadPath.$finalFileName, file_get_contents($realPath));

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
    public function getPublicPathAttribute()
    {
        return url(route('file.get', ['id' => $this->id, 'name' => urlencode($this->filename)]));
    }

    public function getPathAttribute()
    {
        return $this->folder.$this->filename;
    }

    //Statics
    public static function downloadFromUrl($url)
    {
        $pathInfo = pathinfo($url);
        $basename = urldecode($pathInfo['basename']);
        $downloadTmpName = storage_path('tmp').'/'.$basename;

        try{
            $downloadImage = new Client();
            $downloadImage->request('GET', $url, [
                'sink' => $downloadTmpName
            ]);
        }catch(\Exception $e){
            return false;
        }

        $newImage = new UploadedFile($downloadTmpName, $basename, \Illuminate\Support\Facades\File::mimeType($downloadTmpName), \Illuminate\Support\Facades\File::size($downloadTmpName));

        $image = new \Kommercio\Models\File();
        $image->saveFile($newImage);

        unlink($downloadTmpName);

        return $image;
    }
}
