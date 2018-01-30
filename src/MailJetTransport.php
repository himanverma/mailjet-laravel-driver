<?php

namespace MailjetLaravelDriver;

use Illuminate\Support\Facades\Session;
use Swift_Mime_SimpleMessage;
use Illuminate\Filesystem\Filesystem;
use Illuminate\Mail\Transport\Transport;
use \Mailjet\Resources;

class MailJetTransport extends Transport {

    private $userName;
    private $secretKey;

    /**
     * Create a new preview transport instance.
     *
     * @param  string $userName
     * @param  string $secretKey
     *
     * @return void
     */
    public function __construct($userName,$secretKey) {
        $this->userName = $userName;
        $this->secretKey = $secretKey;
    }
    
    
    private function getTo(Swift_Mime_SimpleMessage $message)
    {
        $to = [];
        if ($message->getTo()) {
            $to = array_merge($to, array_keys($message->getTo()));
        }

        if ($message->getCc()) {
            $to = array_merge($to, array_keys($message->getCc()));
        }

        if ($message->getBcc()) {
            $to = array_merge($to, array_keys($message->getBcc()));
        }
        return $to;
    }

    /**
     * {@inheritdoc}
     */
    public function send(Swift_Mime_SimpleMessage $message, &$failedRecipients = null) {
        $toEmailsOnly = $this->getTo($message);
        $to = [];
        foreach($toEmailsOnly as $t){
            $to[] = [
                "Email" => $t
            ];
        }
        $mj = new \Mailjet\Client($this->userName, $this->secretKey,
              true,['version' => 'v3.1']);
        $body = [
            'Messages' => [
                [
                    'From' => [
                        'Email' => array_keys($message->getFrom())[0],
                        'Name' => array_values($message->getFrom())[0]
                    ],
                    'To' => $to,
                    'Subject' => $message->getSubject(),
                    'TextPart' => $message->getBody(),
                    'HTMLPart' => $message->getBody()
                ]
            ]
        ];
        $response = $mj->post(Resources::$Email, ['body' => $body]);
        if($response->getStatus() == 200){
            $result = $response->getBody();
        }else{
            $result =  $response->getBody();
        }
        return $result;
    }

}
