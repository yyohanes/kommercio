<?php

namespace Kommercio\Models;

use Illuminate\Database\Eloquent\Model;
use Kommercio\Models\Interfaces\AuthorSignatureInterface;
use Kommercio\Traits\Model\AuthorSignature;
use Kommercio\Traits\Model\ToggleDate;

class ProductDetail extends Model implements AuthorSignatureInterface
{
    use AuthorSignature;
    use ToggleDate;

    const VISIBILITY_CATALOG = 'catalog';
    const VISIBILITY_SEARCH = 'search';
    const VISIBILITY_EVERYWHERE = 'everywhere';
    const VISIBILITY_NOWHERE = 'nowhere';

    protected $fillable = ['visibility', 'available', 'available_date', 'active', 'active_date', 'retail_price', 'currency', 'tax_group_id', 'store_id', 'product_id', 'taxable','manage_stock'];
    protected $casts = [
        'manage_stock' => 'boolean',
        'taxable' => 'boolean'
    ];
    protected $toggleFields = ['available', 'active'];

    //Scopes
    public function scopeProductEntity($query)
    {
        $query->whereHas('product', function($qb){
            $qb->productEntity();
        });
    }

    //Relations
    public function product()
    {
        return $this->belongsTo('Kommercio\Models\Product');
    }

    //Statics
    public static function getVisibilityOptions($option=null)
    {
        $array = [
            self::VISIBILITY_EVERYWHERE => 'Everywhere',
            self::VISIBILITY_CATALOG => 'Catalog Only',
            self::VISIBILITY_SEARCH => 'Search Only',
            self::VISIBILITY_NOWHERE => 'Nowhere',
        ];

        if(empty($option)){
            return $array;
        }

        return (isset($array[$option]))?$array[$option]:$array;
    }


}
