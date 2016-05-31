<?php

namespace Kommercio\Traits\Model;

use Illuminate\Support\Facades\Auth;

trait AuthorSignature
{
    public function authorSign($type)
    {
        if($type == 'creating'){
            if(Auth::check()){
                $user_id = Auth::user()->id;
            }else{
                $user_id = NULL;
            }

            $this->created_by = $user_id;
            $this->updated_by = $user_id;
        }elseif($type == 'updating'){
            if(Auth::check()){
                $user_id = Auth::user()->id;
            }else{
                $user_id = NULL;
            }

            $this->updated_by = $user_id;
        }
    }

    public function createdBy()
    {
        return $this->belongsTo('Kommercio\Models\User', 'created_by');
    }

    public function updatedBy()
    {
        return $this->belongsTo('Kommercio\Models\User', 'updated_by');
    }
}