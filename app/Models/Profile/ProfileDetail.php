<?php

namespace Kommercio\Models\Profile;

use Illuminate\Database\Eloquent\Model;

class ProfileDetail extends Model
{
    public $timestamps = FALSE;

    //Dummy primary key just so this model can be deleted
    protected $fillable = ['profile_id', 'identifier', 'value'];

    //Relations
    public function profile()
    {
        return $this->belongsTo('Kommercio\Models\Profile')->with('details');
    }
}
