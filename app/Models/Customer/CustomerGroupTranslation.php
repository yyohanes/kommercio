<?php

namespace Kommercio\Models\Customer;

use Illuminate\Database\Eloquent\Model;

class CustomerGroupTranslation extends Model
{
    public $fillable = [
        'name',
        'description',
        'locale',
    ];

    public $timestamps = FALSE;
}
