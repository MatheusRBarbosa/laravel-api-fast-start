<?php

namespace App\Services;


use App\Mails\ForgotPassword;
use Illuminate\Support\Facades\Mail;

class NotificationService extends BaseService
{
    public function __construct()
    {
        //
    }

    /**
     * 
     */
    public function sendForgotEmail($user)
    {
        $template = new ForgotPassword($user);
        $this->sendEmail($user->email, $template);
    }

    /**
     * 
     */
    private function sendEmail($recipient, $template)
    {
        $mailTo = Mail::to($recipient);
        $mailTo->send($template);
    }
}
