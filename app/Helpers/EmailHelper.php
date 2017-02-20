<?php

namespace Kommercio\Helpers;

use Illuminate\Support\Facades\Mail;
use Kommercio\Facades\ProjectHelper as ProjectHelperFacade;
use Kommercio\Models\Store;

class EmailHelper
{
    public function getContact($type='general', $store = null)
    {
        $kommercioDefault = config('kommercio.contacts.'.$type);
        $default = config('project.contacts.'.$type, $kommercioDefault);

        $contact = $default;

        if($store instanceof Store){
            $storeContact = $store->getData('contacts.'.$type, $default);

            if(!empty($storeContact['email'])){
                $contact = $storeContact;
            }
        }

        return $contact;
    }

    public function getTemplate($template)
    {
        $viewPath = ProjectHelperFacade::getViewTemplate('emails.'.$template);

        return $viewPath;
    }

    public function sendMail($to, $subject, $template, $data, $contact='general', $callback = null, $queue = true, $preview=FALSE)
    {
        $contact = $this->getContact($contact, (isset($data['store'])?$data['store']:null));
        $template = $this->getTemplate($template);

        if(!$preview){
            $sendFunction = $queue?'queue':'send';
            $result = Mail::$sendFunction($template, $data, function ($message) use ($contact, $data, $subject, $to, $callback) {
                if ($callback && $callback instanceof \Closure) {
                    call_user_func($callback, $message);
                }

                $message->from($contact['email'], $contact['name']);
                $message->to($to);
                $message->subject($subject);
            });
        }else{
            $result = view($template, $data);
        }

        return $result;
    }
}