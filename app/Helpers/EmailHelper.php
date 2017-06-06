<?php

namespace Kommercio\Helpers;

use Illuminate\Support\Facades\Mail;
use Kommercio\Facades\ProjectHelper as ProjectHelperFacade;
use Kommercio\Mail\DefaultMail;
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
        if(!$preview){
            $sendFunction = $queue?'queue':'send';

            $mail = new DefaultMail($to, $subject, $template, $data, $contact);
            if ($callback && $callback instanceof \Closure) {
                call_user_func($callback, $mail);
            }

            $result = Mail::$sendFunction($mail);
        }else{
            $result = view($template, $data);
        }

        return $result;
    }
}