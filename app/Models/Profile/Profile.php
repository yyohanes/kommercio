<?php

namespace Kommercio\Models\Profile;

use Illuminate\Database\Eloquent\Model;

class Profile extends Model
{
    protected $with = ['details'];
    protected $guarded = [];
    protected $profileDetails = [];

    private $_profileFilled = false;

    //Relations
    public function details()
    {
        return $this->hasMany('Kommercio\Models\Profile\ProfileDetail', 'profile_id');
    }

    public function profileable()
    {
        return $this->morphTo();
    }

    //Methods
    public function getAttribute($key)
    {
        if (array_key_exists($key, $this->profileDetails)) {
            return $this->profileDetails[$key];
        }

        return parent::getAttribute($key);
    }

    public function __isset($key)
    {
        if(array_key_exists($key, $this->profileDetails)){
            return TRUE;
        }

        return parent::__isset($key);
    }

    public function getDetails()
    {
        $this->fillDetails();

        return $this->profileDetails;
    }

    public function fillDetails($refresh = FALSE)
    {
        if(!$this->_profileFilled || $refresh){
            $details = $this->getRelationValue('details');

            $fullNameParts = [];

            foreach($details as $detail){
                $this->profileDetails[$detail->identifier] = $detail->value;
                if(in_array($detail->identifier, ['first_name', 'last_name'])){
                    $fullNameParts[] = $detail->value;
                }
            }

            if($fullNameParts){
                $this->profileDetails['full_name'] = implode(' ', $fullNameParts);
            }

            $this->_profileFilled = TRUE;
        }

        return $this;
    }

    public function saveDetails($details)
    {
        if(!$this->exists){
            throw new \LogicException('Profile doesn\'t exist.');
        }

        if(isset($details['full_name'])){
            $explodedFullName = explode(' ', $details['full_name']);

            $details['first_name'] = array_shift($explodedFullName);

            if(!empty($explodedFullName)){
                $details['last_name'] = implode(' ', $explodedFullName);
            }

            unset($details['full_name']);
        }

        $profileFieldsFromAttributes = array_keys($details);

        $flippedProfiles = array_flip($profileFieldsFromAttributes);

        foreach($this->details as $profile){
            if(!isset($details[$profile->identifier])){
                $profile->delete();
            }else{
                $profile->update([
                    'value' => $details[$profile->identifier]
                ]);
            }

            //Remove updated field from attributes so it won't get processed in next process
            unset($flippedProfiles[$profile->identifier]);
        }

        $flippedProfiles = array_flip($flippedProfiles);

        foreach($flippedProfiles as $newProfile){
            if(trim($details[$newProfile]) !== ''){
                ProfileDetail::create([
                    'identifier' => $newProfile,
                    'value' => $details[$newProfile],
                    'profile_id' => $this->id
                ]);
            }
        }
    }

    //Scopes
    public function scopeJoinAddress($query)
    {
        $profileDetailQuery = $this->details();

        $countryTable = with(new Country())->getTable();
        $stateTable = with(new State())->getTable();
        $cityTable = with(new City())->getTable();
        $districtTable = with(new District())->getTable();
        $areaTable = with(new Area())->getTable();

        $query->leftJoin($profileDetailQuery->getRelated()->getTable().' AS VADDRESS1', function($join) use ($profileDetailQuery){
            $join->on('VADDRESS1.'.$profileDetailQuery->getPlainForeignKey(), '=', $this->getQualifiedKeyName())
                ->where('VADDRESS1.identifier', '=', 'address_1');
        });

        $query->leftJoin($profileDetailQuery->getRelated()->getTable().' AS VADDRESS2', function($join) use ($profileDetailQuery){
            $join->on('VADDRESS2.'.$profileDetailQuery->getPlainForeignKey(), '=', $this->getQualifiedKeyName())
                ->where('VADDRESS2.identifier', '=', 'address_2');
        });

        $query->leftJoin($profileDetailQuery->getRelated()->getTable().' AS VSTATE', function($join) use ($profileDetailQuery){
            $join->on('VSTATE.'.$profileDetailQuery->getPlainForeignKey(), '=', $this->getQualifiedKeyName())
                ->where('VSTATE.identifier', '=', 'state_id');
        })->leftJoin($stateTable.' AS STATE', 'STATE.id', '=', 'VSTATE.value');

        $query->leftJoin($profileDetailQuery->getRelated()->getTable().' AS VCITY', function($join) use ($profileDetailQuery){
            $join->on('VCITY.'.$profileDetailQuery->getPlainForeignKey(), '=', $this->getQualifiedKeyName())
                ->where('VCITY.identifier', '=', 'city_id');
        })->leftJoin($cityTable.' AS CITY', 'CITY.id', '=', 'VCITY.value');

        $query->leftJoin($profileDetailQuery->getRelated()->getTable().' AS VDISTRICT', function($join) use ($profileDetailQuery){
            $join->on('VDISTRICT.'.$profileDetailQuery->getPlainForeignKey(), '=', $this->getQualifiedKeyName())
                ->where('VDISTRICT.identifier', '=', 'district_id');
        })->leftJoin($districtTable.' AS DISTRICT', 'DISTRICT.id', '=', 'VDISTRICT.value');

        $query->leftJoin($profileDetailQuery->getRelated()->getTable().' AS VAREA', function($join) use ($profileDetailQuery){
            $join->on('VAREA.'.$profileDetailQuery->getPlainForeignKey(), '=', $this->getQualifiedKeyName())
                ->where('VAREA.identifier', '=', 'area_id');
        })->leftJoin($areaTable.' AS AREA', 'AREA.id', '=', 'VAREA.value');

        $query->leftJoin($profileDetailQuery->getRelated()->getTable().' AS VPOSTAL', function($join) use ($profileDetailQuery){
            $join->on('VPOSTAL.'.$profileDetailQuery->getPlainForeignKey(), '=', $this->getQualifiedKeyName())
                ->where('VPOSTAL.identifier', '=', 'postal_code');
        });

        $query->selectRaw($this->getTable().'.*, VADDRESS1.value AS address_1, VADDRESS2.value AS address_2, STATE.name AS state, CITY.name AS city, DISTRICT.name AS district, AREA.name AS area, VPOSTAL.value AS postal_code');
    }

    public function scopeWhereField($query, $key, $value, $operator='=')
    {
        $query->whereHas('details', function($qb) use ($key, $value, $operator){
            $qb->where('identifier', $key)
                ->where('value', $operator, $value);
        });
    }

    public function scopeWhereFields($query, $filters, $or=FALSE)
    {
        $method = 'whereHas';

        if($or){
            $method = 'orWhereHas';
        }

        foreach($filters as $idx=>$filter){
            $filter['operator'] = isset($filter['operator'])?$filter['operator']:'=';

            if(in_array($filter['key'], ['state', 'country', 'city', 'district', 'area'])){
                $query->$method('details', function($qb) use ($filter){
                    $addressClass = '\Kommercio\Models\Address\\'.studly_case($filter['key']);
                    $tableName = with(new $addressClass())->getTable();

                    $qb->join($tableName.' AS A', function($join) use ($filter){
                        $join->on('A.id', '=', 'value')->where('identifier', '=', $filter['key'].'_id');
                    })->where('identifier', $filter['key'].'_id')->where('A.name', $filter['operator'], $filter['value']);
                });
            }else{
                $query->$method('details', function($qb) use ($filter){
                    $qb->where('identifier', $filter['key'])
                        ->where('value', $filter['operator'], $filter['value']);
                });
            }
        }
    }
}
