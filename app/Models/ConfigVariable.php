<?php

namespace Kommercio\Models;

use Illuminate\Database\Eloquent\Model;

class ConfigVariable extends Model
{
    public $timestamps = false;
    public $incrementing = false;
    protected $primaryKey = 'key';
    public $fillable = ['key', 'value'];
}
