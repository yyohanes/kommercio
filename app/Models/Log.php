<?php

namespace Kommercio\Models;

use Illuminate\Database\Eloquent\Model;
use Kommercio\Models\Interfaces\AuthorSignatureInterface;
use Kommercio\Traits\Model\AuthorSignature;
use Kommercio\Traits\Model\HasDataColumn;

class Log extends Model implements AuthorSignatureInterface
{
    use AuthorSignature, HasDataColumn;

    protected $fillable = ['tag', 'notes', 'author', 'value'];

    //Relations
    public function loggable()
    {
        return $this->morphTo();
    }

    //Scopes
    public function scopeWhereTag($query, $tags)
    {
        if(is_string($tags)){
            $tags = [$tags];
        }

        $query->whereIn('tag', $tags);
    }

    //Statics
    public static function log($tag, $message, Model $model, $value = null, $userName = null, $data = null)
    {
        $log = new self();
        $log->fill([
            'tag' => $tag,
            'notes' => $message,
            'value' => $value
        ]);

        if(!empty($data) && is_array($data)){
            $log->saveData($data);
        }

        $log->loggable()->associate($model);
        $log->save();

        $log->author = $userName?:($log->createdBy->fullName?:$log->createdBy->email);
        $log->save();

        return $log;
    }
}
