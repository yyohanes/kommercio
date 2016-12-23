<?php

namespace Kommercio\Helpers;

use MailerLiteApi\MailerLite;
use Kommercio\Facades\ProjectHelper as ProjectHelperFacade;

class NewsletterSubscriptionHelper
{
    public function subscribe($group = 'default', $email, $name=null, $last_name = null, $additional = [])
    {
        $defaultNewsletter = ProjectHelperFacade::getConfig('newsletter.default', 'mailerlite');

        if($defaultNewsletter == 'mailerlite'){
            $mailerlite = new MailerLite(ProjectHelperFacade::getConfig('newsletter.'.$defaultNewsletter.'.api_key'));
            $groupsApi = $mailerlite->groups();

            $data = [
                'email' => $email,
                'fields' => [
                    'name' => $name,
                    'last_name' => $last_name
                ]
            ];

            $data['fields'] += $additional;

            $subscriber = $data;

            $group_id = ProjectHelperFacade::getConfig('newsletter.'.$defaultNewsletter.'.subscriber_groups.'.$group, ProjectHelperFacade::getConfig('newsletter.'.$defaultNewsletter.'.subscriber_groups.default'));

            $response = $groupsApi->addSubscriber($group_id, $subscriber);
        }elseif($defaultNewsletter == 'sendgrid'){
            $sendgrid = new \SendGrid(ProjectHelperFacade::getConfig('newsletter.'.$defaultNewsletter.'.api_key'));

            $first_name = $name;

            if(!$last_name){
                $exploded = explode(' ', $name);
                if(count($exploded) > 1){
                    $last_name = array_pop($exploded);
                    $first_name = implode(' ', $exploded);
                }else{
                    $last_name = null;
                }
            }

            $data = [
                [
                    'email' => $email,
                    'first_name' => $first_name,
                    'last_name' => $last_name
                ]
            ];

            $response = $sendgrid->client->contactdb()->recipients()->post($data);
            if(preg_match('/^2/', $response->statusCode())){
                $result = json_decode($response->body());

                $group_id = ProjectHelperFacade::getConfig('newsletter.'.$defaultNewsletter.'.subscriber_groups.'.$group, ProjectHelperFacade::getConfig('newsletter.'.$defaultNewsletter.'.subscriber_groups.default'));

                $response = $sendgrid->client->contactdb()->lists()->_($group_id)->recipients()->post($result->persisted_recipients);
            }
        }
    }

    public function getAllowedGroups()
    {
        $defaultNewsletter = ProjectHelperFacade::getConfig('newsletter.default', 'mailerlite');

        return ProjectHelperFacade::getConfig('newsletter.'.$defaultNewsletter.'.subscriber_groups', []);
    }
}