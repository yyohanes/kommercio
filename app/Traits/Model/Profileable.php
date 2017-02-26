<?php

namespace Kommercio\Traits\Model;

use Kommercio\Models\Profile\Profile;
use Illuminate\Support\Facades\DB;

trait Profileable
{
    public function scopeJoinFullName($query, $name='full_name')
    {
        $profileDetailQuery = with(new Profile())->details();

        $query->leftJoin($profileDetailQuery->getRelated()->getTable().' AS VFNAME', function($join) use ($profileDetailQuery){
            $join->on('VFNAME.'.$profileDetailQuery->getPlainForeignKey(), '=', $this->getTable().'.'.$this->profile()->getForeignKey())
                ->where('VFNAME.identifier', '=', 'first_name');
        });

        $query->leftJoin($profileDetailQuery->getRelated()->getTable().' AS VLNAME', function($join) use ($profileDetailQuery){
            $join->on('VLNAME.'.$profileDetailQuery->getPlainForeignKey(), '=', $this->getTable().'.'.$this->profile()->getForeignKey())
                ->where('VLNAME.identifier', '=', 'last_name');
        });

        $query->selectRaw($this->getTable().'.*, CONCAT(VFNAME.value, " ", VLNAME.value) AS '.$name);
    }

    public function scopeJoinFields($query, $fields)
    {
        $profileDetailQuery = with(new Profile())->details();

        foreach($fields as $field){
            $query->leftJoin($profileDetailQuery->getRelated()->getTable().' AS JOIN_'.$field, function($join) use ($profileDetailQuery, $field){
                $join->on('JOIN_'.$field.'.'.$profileDetailQuery->getPlainForeignKey(), '=', $this->getTable().'.'.$this->profile()->getForeignKey())
                    ->where('JOIN_'.$field.'.identifier', '=', $field);
            });

            $query->addSelect(DB::raw('JOIN_'.$field.'.value AS '.$field));
        }
    }

    public function scopeWhereField($query, $key, $value, $operator='=')
    {
        $query->whereHas('profile', function($qb) use ($key, $value, $operator){
            $qb->whereField($key, $value, $operator);
        });
    }

    public function scopeWhereFields($query, $filters, $or=FALSE)
    {
        $query->whereHas('profile', function($qb) use ($filters, $or){
            $qb->whereFields($filters, $or);
        });
    }

    public function scopeOrderByField($query, $key, $dir='DESC')
    {
        $profileDetailQuery = with(new Profile())->details();

        $query->leftJoin($profileDetailQuery->getRelated()->getTable().' AS OV', function($join) use ($profileDetailQuery, $key){
            $join->on('OV.'.$profileDetailQuery->getPlainForeignKey(), '=', $this->getTable().'.'.$this->profile()->getForeignKey())
                ->where('OV.identifier', '=', $key);
        })
        ->orderBy('OV.value', $dir);
    }

    public function getProfile()
    {
        $profile = $this->profile;

        if(!$profile){
            $profile = new Profile();
        }

        return $profile;
    }

    public function saveProfile($data)
    {
        $profile = $this->profile;

        if(!$this->exists){
            throw new \LogicException('Parent model doesn\'t exist.');
        }

        if(!$profile){
            $profile = new Profile();
            $profile->profileable()->associate($this);
            $profile->save();

            $this->profile()->associate($profile);
            $this->save();
        }

        $profile->saveDetails($data);
        $this->load('profile');
    }

    public function loadProfileFields()
    {
        return $this->getProfile()->fillDetails();
    }

    public function profile()
    {
        return $this->belongsTo('Kommercio\Models\Profile\Profile');
    }

    public function profiles()
    {
        return $this->morphMany('Kommercio\Models\Profile\Profile', 'profileable');
    }

    public function getAttribute($key)
    {
        $attribute = parent::getAttribute($key);

        if(property_exists($this, 'profileKeys') && in_array($key, $this->profileKeys)){
            if($attribute){
                $attribute->fillDetails();
            }
        }

        return $attribute;
    }

    //Statics
    protected static function boot()
    {
        parent::boot();

        static::deleting(function($model){
            foreach($model->profiles as $profile){
                $profile->delete();
            }

            $user = $model->user;

            if($user){
                $user->delete();
            }
        });
    }
}