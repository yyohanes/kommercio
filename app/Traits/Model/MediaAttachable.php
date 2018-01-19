<?php

namespace Kommercio\Traits\Model;

use Kommercio\Models\Media;

trait MediaAttachable{
    public function media($type=null){
        $qb = $this->morphToMany('Kommercio\Models\Media', 'media_attachable')->withPivot(['caption', 'type', 'locale']);
        if(!empty($type)){
            $qb->where('type', $type);
        }

        return $qb;
    }

    public function attachMedia($media, $type=null, $markPermanent=true)
    {
        foreach($media as $mediaIdx=>$medium){
            $this->media($type)->attach([$mediaIdx => $medium]);
        }

        if($markPermanent){
            $this->media($type)->update([
                'temp' => false
            ]);
        }

        $this->save();
    }

    public function detachMedia($media, $type=null)
    {
        foreach($media as $medium){
            $this->media($type)->detach($medium);
        }

        $this->save();
    }

    public function syncMedia($media, $type=null, $markPermanent=true)
    {
        $existingMedia = $this->media($type)->pluck('id')->all();
        foreach($existingMedia as $existingMedium){
            $this->media($type)->newPivotStatementForId($existingMedium)->where('type', $type)->delete();
        }

        $this->attachMedia($media, $type, $markPermanent);

        $newMedia = array_keys($media);
        $expiredMedia = array_diff($existingMedia, array_keys($newMedia));
        foreach($expiredMedia as $expiredMedium){
            $file = Media::find($expiredMedium);
            if($file && !$file->isUsed()){
                $file->delete();
            }
        }

        $this->save();
    }

    public function clearMedia($type, $delete=FALSE)
    {
        $rows = $this->media($type)->get();

        foreach($rows as $row){
            $this->media($type)->newPivotStatementForId($row->id)->where('type', $type)->delete();

            if($delete){
                if(!$row->isUsed()){
                    $row->delete();
                }
            }
        }

        $this->save();
    }

    public function deleteMedia($type)
    {
        $this->clearMedia($type, TRUE);
        $this->save();
    }
}