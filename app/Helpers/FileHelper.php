<?php

namespace Kommercio\Helpers;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Ramsey\Uuid\Uuid;

class FileHelper {

    /**
     * Storage driver to use
     * @var string
     */
    private $storageDriver = 's3';

    /**
     * Helper function to save to DB & upload to S3
     *
     * @param UploadedFile $uploadedFile
     * @param $folder
     * @param null $name
     * @param string $visibility
     * @throws \Throwable
     * @return string|null
     */
    public function saveUploadedFile(UploadedFile $uploadedFile, $folder, $name = null, $visibility = 'public') {
        if (empty($name)) {
            $name = Uuid::uuid4() . '.' . $uploadedFile->getClientOriginalExtension();
        }

        // If file already exists, reupload as empty name
        if ($this->getStorage()->exists($folder . '/' . $name)) {
            $updatedName = Uuid::uuid4() . '-' . $name;
            return $this->saveUploadedFile($uploadedFile, $folder, $updatedName, $visibility);
        }

        try {
            $storedPath = $this->getStorage()->putFileAs($folder, $uploadedFile, $name, $visibility);

            return $this->getStorage()->url($storedPath);
        } catch (\Throwable $e) {
            report($e);

            return null;
        }
    }

    protected function getStorage() {
        return Storage::disk($this->storageDriver);
    }
}
