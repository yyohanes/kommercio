<?php

namespace Kommercio\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Contracts\Queue\ShouldQueue;
use Kommercio\Facades\ProjectHelper;
use Kommercio\Models\Store;

class DefaultMail extends Mailable
{
    use Queueable, SerializesModels;

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct($to, $subject, $view, $viewData = [], $contact = 'general')
    {
        $this->to = [];
        if(!is_array($to)){
            $to = explode(',', $to);
        }

        foreach($to as $toAddress){
            $this->to[] = [
                'address' => $toAddress,
                'name' => null
            ];
        }

        $this->subject = $subject;
        $this->view = $this->getTemplate($view);
        $this->viewData = $viewData;

        $contact = $this->getContact($contact, !empty($viewData['store'])?$viewData['store']:null);
        $this->from = [
            [
                'address' => $contact['email'],
                'name' => $contact['name']
            ]
        ];
    }

    /**
     * Get Contact
     *
     * @param string $type
     * @param null $store
     * @return array
     */
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

    /**
     * Get view template
     *
     * @param $template
     * @return string
     */
    public function getTemplate($template)
    {
        $viewPath = ProjectHelper::getViewTemplate('emails.'.$template);

        return $viewPath;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        return $this;
    }
}
