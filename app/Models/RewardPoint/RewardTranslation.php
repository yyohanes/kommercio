<?php

namespace Kommercio\Models\RewardPoint;

use Illuminate\Database\Eloquent\Model;
use Kommercio\Traits\Model\MediaAttachable;

class RewardTranslation extends Model
{
    use MediaAttachable;

    public $fillable = [
        'name',
        'description',
        'locale',
    ];

    public $timestamps = FALSE;

    //Relations
    public function images()
    {
        return $this->media('image')->where('locale', $this->locale);
    }
}
