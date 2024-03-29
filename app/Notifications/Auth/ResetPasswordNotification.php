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
     * Create a new notification instance.
     *
     * @var User $user
     * @return void
     */
    public function __construct(User $user, $token)
    {
        $this->user = $user;
        $this->token = $token;
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
//        return (new MailMessage)
//                    ->view('auth.emails.password', [
//                        'user' => $this->user,
//                        'token' => $this->token
//                    ]);

        $subjectKey = LanguageHelper::getTranslationKey('passwords.password_reset_subject');
        $mailable = new DefaultMail(
                    $notifiable->email,
                    trans($subjectKey),
                    'auth.password',
                    ['user' => $this->user, 'token' => $this->token]);

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
