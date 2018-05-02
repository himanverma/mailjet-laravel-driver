<?php

namespace MailjetLaravelDriver;

use Illuminate\Support\Facades\Session;
use Swift_Mime_SimpleMessage;
use Illuminate\Filesystem\Filesystem;
use Illuminate\Mail\Transport\Transport;
use Illuminate\Support\Facades\Log;
use \Mailjet\Resources;

class MailJetTransport extends Transport {

    private $userName;
    private $secretKey;
    private $headers;

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
        $this->headers = [];
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
     * Gets all the headers from the message
     * @param $message
     * @return array
     */
    private function getHeaders($message)
    {
        $this->headers = $message->getHeaders()->getAll();
        return $this->headers;
    }

    /**
     * Gets a custom header by field name
     * @param $fieldname
     * @return null
     */
    private function getCustomHeader($fieldname)
    {
        foreach($this->headers as $h)
        {
            if(get_class($h) == 'Swift_Mime_Headers_UnstructuredHeader' && $h->getFieldName() == $fieldname)
            {
                return $h->getValue();
            }
        }

        return null;
    }

    /**
     * {@inheritdoc}
     */
    public function send(Swift_Mime_SimpleMessage $message, &$failedRecipients = null) {
        $headers = $this->getHeaders($message);
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
                    'HTMLPart' => $message->getBody(),
                ]
            ]
        ];

        $campaign = $this->getCustomHeader('X-Mailjet-Campaign');
        if(!is_null($campaign))
        {
            $body['Messages'][0]['CustomCampaign'] = $campaign;
        }

        $template = $this->getCustomHeader('X-MailjetLaravel-Template');
        if(!is_null($template))
        {
            $body['Messages'][0]['TemplateLanguage'] = true;
            $body['Messages'][0]['TemplateID'] = $template;
            $body['Messages'][0]['Variables'] = json_decode($this->getCustomHeader('X-MailjetLaravel-TemplateBody'));

            //unset
            unset($body['Messages'][0]['HTMLPart']);
            unset($body['Messages'][0]['TextPart']);
        }

        $response = $mj->post(Resources::$Email, ['body' => $body]);
        if($response->getStatus() == 200){
            $result = $response->getBody();
        }else{
            $result = $response->getBody();
            Log::error('Mailjet Error: '.json_encode($result));
        }
        return $result;
    }

}
