<?php

namespace Kommercio\Models\PriceRule;

use Illuminate\Database\Eloquent\Model;
use Kommercio\Models\Interfaces\AuthorSignatureInterface;
use Kommercio\Traits\Model\AuthorSignature;
use Kommercio\Traits\Model\HasDataColumn;

class Coupon extends Model implements AuthorSignatureInterface
{
    use AuthorSignature, HasDataColumn;

    protected $fillable = ['coupon_code'];

    //Relations
    public function cartPriceRule()
    {
        return $this->belongsTo('Kommercio\Models\PriceRule\CartPriceRule');
    }

    public function customer()
    {
        return $this->belongsTo('Kommercio\Models\Customer');
    }
}
