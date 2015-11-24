<?php namespace MusicEquity\Helpers;
use Mail;

/**
 * Class Mailer
 * Simple Mail Service
 * @package services\mail\Mailer
 */
class Mailer
{

    /**
     * Sending an email
     *
     * @param $user
     * @param $template
     * @param $data
     * @param $params
     */
    public function send($user, $template, $data, $params){
        $info = array (
          'email' => $user->email
        );

        Mail::queue($template, $data, function($message) use ($info, $params)
        {
            if(isset($params['from'])) {
                $message->from($params['from'], 'Music Equity');
            }
            $message->to($info['email'])->subject($params['subject']);

        });
    }
}