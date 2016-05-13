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
}
