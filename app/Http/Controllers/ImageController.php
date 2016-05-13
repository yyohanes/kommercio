<?php

namespace Kommercio\Http\Controllers;

use Illuminate\Support\Facades\Storage;
use Kommercio\Models\Media;
use League\Glide\ServerFactory;
use Spatie\Glide\Controller\GlideImageController;
use League\Flysystem\Filesystem;
use League\Flysystem\Adapter\Local;

class ImageController extends GlideImageController
{
    public function style($style, $image)
    {
        $this->validateSignature();

        $this->writeIgnoreFile();

        //Previouse version use Local storage
        //$server = $this->setGlideServer($this->setImageSource(), $this->setImageCache(), $api);

        //Update to storage based on image
        $file = Media::whereRaw('CONCAT(folder, filename) LIKE ?', [$image])->firstOrFail();

        $server = $this->setGlideServer($this->setImageSource($file->storage), $this->setImageCache($style), $style);

        return $server->outputImage($this->request->path(), $this->getPresets($style));
    }

    protected function getPresets($style)
    {
        $styles = config('kommercio.image_styles');

        return $styles[$style];
    }

    protected function setGlideServer($source, $cache, $style)
    {
        $imagePath = config('kommercio.images_path');

        $server = ServerFactory::create([
            'base_url' => $imagePath.'/'.$style,
            'source' => $source,
            'cache' => $cache
        ]);

        $server->setBaseUrl($imagePath.'/'.$style);

        return $server;
    }

    /**
     *  Set the source path for images
     *
     * @return Filesystem
     */
    protected function setImageSource($source='local')
    {
        return Storage::disk($source)->getDriver();
    }

    /**
     * Set the cache folder
     *
     * @return Filesystem
     */
    protected function setImageCache($path='')
    {
        return (new Filesystem(new Local(
            $this->glideConfig['cache']['path'].'/'.$path
        )));
    }
}
