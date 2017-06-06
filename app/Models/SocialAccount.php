<?php

namespace Kommercio\Models;

use Illuminate\Database\Eloquent\Model;
use Kommercio\Facades\NewsletterSubscriptionHelper;
use Laravel\Socialite\Contracts\User as SocialiteUser;

class SocialAccount extends Model
{
    protected $fillable = ['provider', 'provider_user_id'];

    //Relations
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    //Statics
    public static function createOrGetUser(SocialiteUser $socialiteUser, $provider)
    {
        $account = static::where('provider', $provider)
            ->where('provider_user_id', $socialiteUser->getId())
            ->first();

        if($account){
            return $account->user;
        }else{
            $account = new static([
                'provider' => $provider,
                'provider_user_id' => $socialiteUser->getId()
            ]);

            $user = User::where('email', $socialiteUser->getEmail())->first();

            if (!$user) {
                $accountData = [
                    'email' => $socialiteUser->getEmail(),
                    'status' => User::STATUS_ACTIVE
                ];

                $profileData = [
                    'full_name' => $socialiteUser->getName(),
                    'email' => $socialiteUser->getEmail()
                ];

                $customer = Customer::saveCustomer($profileData, $accountData, true, true);
                $user = $customer->user;
            }

            $account->user()->associate($user);
            $account->save();

            return $user;
        }
    }
}
