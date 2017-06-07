<?php

namespace Kommercio\Listeners;

use Kommercio\Events\ProductPriceRuleEvent;
use Kommercio\Models\PriceRule;

class ProductPriceRuleListener
{
    /**
     * Handle the event.
     *
     * @param  ProductPriceRuleEvent  $event
     * @return void
     */
    public function handle(ProductPriceRuleEvent $event)
    {
        $type = $event->type;

        if($type == 'will_change_products') {
            $this->clearProductsCache($event->priceRule);
        }elseif($type == 'did_change_products') {
            $this->clearProductsCache($event->priceRule);
        }
    }

    /**
     * Clear cache of products related to Price Rule
     * @param PriceRule $priceRule
     */
    protected function clearProductsCache(PriceRule $priceRule)
    {
        // If $priceRule has store, we tell product to use this store for getting productDetail
        $store = $priceRule->store;

        foreach($priceRule->getProducts() as $product){
            if($store){
                $product->store = $store;
            }

            $product->touch();
        }
    }
}
