<?php

namespace Kommercio\Models\Customer;

use Illuminate\Database\Eloquent\Model;
use Kommercio\Models\Customer;
use Kommercio\Models\Product;

class Bookmark extends Model
{
    protected $fillable = ['name'];

    //Methods
    public function add($productId)
    {
        $products = $this->products->pluck('id');
        $products->push($productId);

        $newProducts = [];
        foreach($products->unique()->values() as $idx => $product){
            $newProducts[$product] = [
                'sort_order' => $idx
            ];
        }

        return $this->products()->sync($newProducts);
    }

    public function remove($productId)
    {
        $products = $this->products->pluck('id', 'id');

        if($products->contains($productId)){
            $products->forget($productId);
        }

        $newProducts = [];
        foreach($products->values() as $idx => $product){
            $newProducts[$product] = [
                'sort_order' => $idx
            ];
        }

        return $this->products()->sync($newProducts);
    }

    //Scope
    public function scopeBelongsToCustomer($query, $customer)
    {
        $query->where('customer_id', $customer->id);
    }

    //Relations
    public function customer()
    {
        return $this->belongsTo('Kommercio\Models\Customer');
    }

    public function products()
    {
        return $this->belongsToMany('Kommercio\Models\Product')->withPivot(['sort_order'])->withTimestamps();
    }

    public function bookmarkType()
    {
        return $this->belongsTo('Kommercio\Models\Customer\BookmarkType');
    }

    //Static
    public static function getOrNew(Customer $customer, $type = null)
    {
        if($type){
            $bookmarkType = null;

            if(is_int($type)){
                $bookmarkType = BookmarkType::find($type);
            }elseif(is_string($type)){
                $bookmarkType = BookmarkType::findBySlug($type);
            }elseif($type instanceof BookmarkType){
                $bookmarkType = $type;
            }
        }else{
            $bookmarkType = BookmarkType::where('default', true)->first();
        }

        if(!$bookmarkType){
            $bookmarkType = BookmarkType::create([
                'name' => $type
            ]);
        }

        $bookmark = $customer->bookmarks->filter(function($bookmark) use ($bookmarkType){
            return $bookmark->bookmarkType->id == $bookmarkType->id;
        })->first();

        if(!$bookmark){
            $bookmark = new Bookmark([
                'name' => $bookmarkType->name,
            ]);
            $bookmark->bookmarkType()->associate($bookmarkType);

            $customer->bookmarks()->save($bookmark);
        }

        return $bookmark;
    }
}
