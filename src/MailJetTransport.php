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
    
    /**
     * {@inheritdoc}
     */
    public function send(Swift_Mime_SimpleMessage $message, &$failedRecipients = null) {
        $to = [];
        $_to = $message->getTo();
        if (count($_to) > 0) {
            foreach ($_to as $key => $val) {
                array_push($to, [
                    'Email' => $key,
                    'Name' => $key
                    ]);
            }
        }
        $cc = [];
        $_cc = $message->getCc();
        if ($_cc && count($_cc) > 0) {
            foreach ($_cc as $key => $val) {
                array_push($cc, [
                    'Email' => $key,
                    'Name' => $key
                    ]);
            }
        } else {
            $_cc = [];
        }
        $bcc = [];
        $_bcc = $message->getBcc();
        if ($_bcc && count($_bcc) > 0) {
            foreach ($_bcc as $key => $val) {
                array_push($bcc, [
                    'Email' => $key,
                    'Name' => $key
                    ]);
            }
        } else {
            $_bcc = [];
        }
        $mj = new \Mailjet\Client($this->userName, $this->secretKey,
              true, ['version' => 'v3.1']);

        $textpart = null;
        $attachments = [];
        if (count($children = $message->getChildren()) > 0) {
            $i = 0;
            foreach ($children as $child) {
                if ($i++ == 0 && $child->getContentType() == 'text/plain') {
                    $textpart = $child->getBody();
                    continue;
                }
                $newattachment = [];
                $newattachment['ContentType']   = $child->getContentType();
                $newattachment['Filename']      = $child->getFilename();
                $newattachment['Base64Content'] = base64_encode($child->getBody());
                array_push($attachments, $newattachment);
            }
        }
        if (!$textpart) {
            $textpart = $message->getBody();
        }
        $body = [
            'Messages' => [
                [
                    'From' => [
                        'Email' => array_keys($message->getFrom())[0],
                        'Name' => array_values($message->getFrom())[0]
                    ],
                    'To' => $to,
                    'Cc' => $cc,
                    'Bcc' => $bcc,
                    'Subject' => $message->getSubject(),
                    'TextPart' => $textpart,
                    'HTMLPart' => $message->getBody(),
                    'Attachments' => $attachments
                ]
            ]
        ];
        $response = $mj->post(Resources::$Email, ['body' => $body]);
        if ($response->getStatus() == 200) {
            $result = $response->getBody();
        } else { 
            $result =  $response->getBody();
        }
        return $result;
    }

}

