<?php

namespace MailjetLaravelDriver;

use Illuminate\Support\Facades\Session;
use Swift_Mime_SimpleMessage;
use Illuminate\Filesystem\Filesystem;
use Illuminate\Mail\Transport\Transport;

class PreviewTransport extends Transport {

    private $userName = "c2029eaf1cd24d1fe9b84ef4fc3ba578";
    private $secretKey = "c4c85a125438ab7e2db1b1a7b6f8081f";

    /**
     * The Filesystem instance.
     *
     * @var \Illuminate\Filesystem\Filesystem
     */
    protected $files;

    /**
     * Get the preview path.
     *
     * @var string
     */
    protected $previewPath;

    /**
     * Time in seconds to keep old previews.
     *
     * @var int
     */
    private $lifeTime;

    /**
     * Create a new preview transport instance.
     *
     * @param  \Illuminate\Filesystem\Filesystem $files
     * @param  string $previewPath
     * @param  int $lifeTime
     *
     * @return void
     */
    public function __construct(Filesystem $files, $previewPath, $lifeTime = 60) {
        $this->files = $files;
        $this->previewPath = $previewPath;
        $this->lifeTime = $lifeTime;
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
//        dd($message->);
        $toEmailsOnly = $this->getTo($message);
        $to = [];
        foreach($toEmailsOnly as $t){
            $to = [
                "Email" => $t
            ];
        }
        $mj = new \Mailjet\Client($this->userName, $this->secretKey,
              true,['version' => 'v3.1']);
        $body = [
            'Messages' => [
                [
                    'From' => [
                        'Email' => $message->getFrom(),
//                        'Name' => "Mailjet Pilot"
                    ],
                    'To' => $to,
                    'Subject' => $message->getSubject(),
                    'TextPart' => $message->toString(),
                    'HTMLPart' => $message->toString()
                ]
            ]
        ];
        $response = $mj->post(Resources::$Email, ['body' => $body]);
        return $response->success() && var_dump($response->getData());
    }

    /**
     * Get the path to the email preview file.
     *
     * @param  \Swift_Mime_SimpleMessage $message
     *
     * @return string
     */
    protected function getPreviewFilePath(Swift_Mime_SimpleMessage $message) {
        $to = str_replace(['@', '.'], ['_at_', '_'], array_keys($message->getTo())[0]);

        $subject = $message->getSubject();

        return $this->previewPath . '/' . str_slug($message->getDate()->getTimestamp() . '_' . $to . '_' . $subject, '_');
    }

    /**
     * Get the HTML content for the preview file.
     *
     * @param  \Swift_Mime_SimpleMessage $message
     *
     * @return string
     */
    protected function getHTMLPreviewContent(Swift_Mime_SimpleMessage $message) {
        $messageInfo = $this->getMessageInfo($message);

        return $messageInfo . $message->getBody();
    }

    /**
     * Get the EML content for the preview file.
     *
     * @param  \Swift_Mime_SimpleMessage $message
     *
     * @return string
     */
    protected function getEMLPreviewContent(Swift_Mime_SimpleMessage $message) {
        return $message->toString();
    }


   
}
