<?php

namespace Kommercio\Models\PriceRule;

use Illuminate\Database\Eloquent\Model;
use Kommercio\Models\Interfaces\AuthorSignatureInterface;
use Kommercio\Models\Order\Order;
use Kommercio\Traits\Model\AuthorSignature;
use Kommercio\Traits\Model\HasDataColumn;

class Coupon extends Model implements AuthorSignatureInterface
{
    use AuthorSignature, HasDataColumn;

    protected $fillable = ['coupon_code', 'max_usage'];

    //Methods
    public function getUsage()
    {
        return $this->cartPriceRule->getUsageByCoupon($this);
    }

    //Get cart price rule and tie it with specific Coupon
    public function getCartPriceRule()
    {
        $this->cartPriceRule->coupon = $this;

        return $this->cartPriceRule;
    }

    //Relations
    public function cartPriceRule()
    {
        return $this->belongsTo('Kommercio\Models\PriceRule\CartPriceRule');
    }

    public function customer()
    {
        return $this->belongsTo('Kommercio\Models\Customer');
    }

    //Statics
    public static function getCouponByCode($coupon_code)
    {
        $qb = self::where('coupon_code', $coupon_code);

        return $qb->first();
    }
}
