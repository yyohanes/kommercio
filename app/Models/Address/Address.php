<?php

namespace Kommercio\Models\Address;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;
use Kommercio\Models\Interfaces\CacheableInterface;

class Address extends Model implements CacheableInterface
{
    public $timestamps = false;

    protected $guarded = ['parent_id'];
    protected $casts = [
        'active' => 'boolean',
        'has_descendant' => 'boolean',
        'show_custom_city' => 'boolean',
    ];
    public $addressType;
    public $parentType;
    public $parentClass;
    public $childType;
    public $childClass;

    public function __construct($attributes = [])
    {
        parent::__construct($attributes);

        $this->addressType = get_class($this);

        if ($pos = strrpos($this->addressType, '\\')){
            $this->addressType = substr($this->addressType, $pos + 1);
        }

        $this->addressType = strtolower($this->addressType);

        if($this->addressType == 'area'){
            $this->guarded[] = 'has_descendant';
        }

        $relationBaseOptions = $this->getClassInfoByType($this->addressType);
        $this->parentType = $relationBaseOptions[1];
        $this->childType = $relationBaseOptions[2];

        $parentRelationBaseOptions = $this->getClassInfoByType($this->parentType);
        $this->parentClass = $parentRelationBaseOptions[0];

        $childRelationBaseOptions = $this->getClassInfoByType($this->parentClass);
        $this->childClass = $childRelationBaseOptions[0];
    }

    //Scopes
    public function scopeActive($query)
    {
        $query->where('active', true);
    }

    //Methods

    /**
     * @inheritdoc
     */
    public function getCacheKeys()
    {
        $tableName = $this->getTable();
        $keys = [
            $tableName . '_' . $this->id,
        ];

        return $keys;
    }

    public function setParent($parent_id)
    {
        if($this->parentType){
            $field = $this->parentType.'_id';
            $this->$field = $parent_id;
        }
    }

    public function getParent()
    {
        if($this->parentType){
            $parentType = $this->parentType;

            return $this->$parentType;
        }

        return null;
    }

    public function getChildren()
    {
        if($this->childType){
            $childType = str_plural($this->childType);

            return $this->$childType;
        }

        return [];
    }

    public function getType()
    {
        return strtolower(get_class($this));
    }

    public function getSteps()
    {
        return [];
    }

    //Statics
    public static function getClassInfoByType($type)
    {
        $type = strtolower($type);
        $name = null;
        $parentType = null;
        $childType = null;

        switch($type){
            case 'country':
                $name = 'Country';
                $childType = 'state';
                break;
            case 'state':
                $name = 'State';
                $parentType = 'country';
                $childType = 'city';
                break;
            case 'city':
                $name = 'City';
                $parentType = 'state';
                $childType = 'district';
                break;
            case 'district':
                $name = 'District';
                $parentType = 'city';
                $childType = 'area';
                break;
            case 'area':
                $name = 'Area';
                $parentType = 'district';
                break;
        }

        return [$name, $parentType, $childType];
    }

    public static function getClassNameByType($type)
    {
        $type = self::getClassInfoByType($type)[0];

        $fqn = self::class;

        if ($pos = strrpos($fqn, '\\')){
            $fqn = substr($fqn, 0, $pos);
        }

        $fqn = $fqn.'\\'.$type;

        return $fqn;
    }

    public static function getAddressByType($id, $type)
    {
        $model = self::getClassNameByType($type);
    }

    public static function findById(int $id)
    {
        $tableName = (new static)->getTable();

        return Cache::remember($tableName . '_' . $id, 3600, function() use ($id) {
            return static::where('id', $id)->first();
        });
    }
}
