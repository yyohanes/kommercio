<?php

namespace Kommercio\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class Media extends File
{
    protected $attachable_table = 'media_attachables';
    protected $table = 'files';

    public function isUsed()
    {
        $useCount = DB::table($this->attachable_table)->where('media_id', $this->id)->count();

        return $useCount > 0;
    }

    public function getImagePath($size)
    {
        $path = $this->path;

        return config('kommercio.images_path').'/'.$size.'/'.$path;
    }

    public function getCaptionAttribute()
    {
        return $this->pivot->caption;
    }
}
