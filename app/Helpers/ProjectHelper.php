<?php

namespace Kommercio\Helpers;

use Illuminate\Support\Facades\Request;
use Kommercio\Models\File;
use Kommercio\Models\Store;

class ProjectHelper
{
    public function getMaxUploadSize()
    {
        return intval(File::MAXIMUM_SIZE);
    }

    public function getActiveStore()
    {
        $defaultStore = Store::where('default', 1)->first();

        return $defaultStore;
    }
}