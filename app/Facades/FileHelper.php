<?php

namespace Kommercio\Facades;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Facade;

/**
 * @method static saveUploadedFile(UploadedFile $file, string $folder, string $name = null, string $visibility = 'public')
 */
class FileHelper extends Facade
{
    protected static function getFacadeAccessor()
    {
        return \Kommercio\Helpers\FileHelper::class;
    }
}
