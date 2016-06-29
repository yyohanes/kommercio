<?php

namespace Kommercio\Helpers;

use Illuminate\Support\Facades\Mail;
use Kommercio\Facades\ProjectHelper as ProjectHelperFacade;

class EmailHelper
{
    public function getContact($type='general')
    {
        $default = config('kommercio.contacts.'.$type);
        $contact = config('project.contacts.'.$type, $default);

        return $contact;
    }

    public function getTemplate($template)
    {
        $viewPath = ProjectHelperFacade::getViewTemplate('emails.'.$template);

        return $viewPath;
    }

    public function sendMail($to, $subject, $template, $data, $contact='general', $preview=FALSE)
    {
        $contact = $this->getContact($contact);
        $template = $this->getTemplate($template);

        if(!$preview){
            $result = Mail::send($template, $data, function ($message) use ($contact, $data, $subject, $to) {
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