<?php

namespace Kommercio\Helpers;

use MailerLiteApi\MailerLite;
use Kommercio\Facades\ProjectHelper as ProjectHelperFacade;

class NewsletterSubscriptionHelper
{
    public function subscribe($group = 'default', $email, $name=null, $last_name = null, $additional = [])
    {
        $mailerlite = new MailerLite(ProjectHelperFacade::getConfig('mailerlite_api_key'));
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

        $group_id = ProjectHelperFacade::getConfig('mailerlite_subscriber_groups.'.$group, ProjectHelperFacade::getConfig('mailerlite_subscriber_groups.default'));

        $response = $groupsApi->addSubscriber($group_id, $subscriber);
    }
}