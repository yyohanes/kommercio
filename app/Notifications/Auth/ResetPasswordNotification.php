<?php

namespace Kommercio\Notifications\Auth;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Kommercio\Facades\LanguageHelper;
use Kommercio\Mail\DefaultMail;
use Kommercio\Models\User;

class ResetPasswordNotification extends Notification
{
    use Queueable;

    /**
     * The password reset token.
     *
     * @var string
     */
    public $token;

    /**
     * @var User
     */
    public $user;

    /**
     * @var string
     */
    public $redirectTo;

    /**
     * Create a new notification instance.
     *
     * @return void
     */
    public function __construct(User $user, $token, $redirectTo = null)
    {
        $this->user = $user;
        $this->token = $token;
        $this->redirectTo = $redirectTo;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @param  mixed  $notifiable
     * @return array
     */
    public function via($notifiable)
    {
        return ['mail'];
    }

    /**
     * Get the mail representation of the notification.
     *
     * @param  mixed  $notifiable
     * @return \Illuminate\Notifications\Messages\MailMessage
     */
    public function toMail($notifiable)
    {
        $subjectKey = LanguageHelper::getTranslationKey('passwords.password_reset_subject');

        $mailData = [
            'user' => $this->user,
            'token' => $this->token,
            'redirectTo' => $this->redirectTo,
        ];

        $mailable = new DefaultMail(
                    $notifiable->email,
                    trans($subjectKey),
                    'auth.password',
                    $mailData);

        return $mailable;
    }

    /**
     * Get the array representation of the notification.
     *
     * @param  mixed  $notifiable
     * @return array
     */
    public function toArray($notifiable)
    {
        return [
            //
        ];
    }
}
