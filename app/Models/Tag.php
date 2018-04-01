<?php

namespace Kommercio\Models;

use Illuminate\Support\Facades\Cache;
use Kommercio\Models\Abstracts\SluggableModel;
use Kommercio\Models\Interfaces\AuthorSignatureInterface;
use Kommercio\Models\Interfaces\CacheableInterface;
use Kommercio\Traits\Model\AuthorSignature;

class Tag extends SluggableModel implements AuthorSignatureInterface, CacheableInterface
{
    use AuthorSignature;

    protected $fillable = ['name', 'slug', 'notes'];

    // Static
    public static function findBySlug($slug) {
        $tableName = (new static)->getTable();

        return Cache::remember($tableName . '_' . $slug, 25200, function() use ($slug) {
            return static::where('slug', $slug)->first();
        });
    }

    /**
     * @inheritdoc
     */
    public function getCacheKeys()
    {
        $tableName = $this->getTable();
        $keys = [
            $tableName . '_' . $this->slug,
        ];

        return $keys;
    }
}
