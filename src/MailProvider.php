<?php

namespace Themsaid\MailPreview;

use Illuminate\Foundation\Http\Kernel;
use Illuminate\Mail\MailServiceProvider;
use Illuminate\Support\ServiceProvider;
use Swift_Mailer;

class MailProvider extends MailServiceProvider
{
    /**
     * Register the Swift Mailer instance.
     *
     * @return void
     */
    function registerSwiftMailer()
    {
        if ($this->app['config']['mail.driver'] == 'mailjet') {
            $this->registerPreviewSwiftMailer();
        } else {
            parent::registerSwiftMailer();
        }
    }

    /**
     * Register the Preview Swift Mailer instance.
     *
     * @return void
     */
    protected function registerPreviewSwiftMailer()
    {
//        print_r("booting 1235..."); exit;
        $this->app->singleton('swift.mailer', function($app) {
            return new Swift_Mailer(
                new PreviewTransport(
                    $app->make('Illuminate\Filesystem\Filesystem'),
                    $app['config']['mailpreview.path'],
                    $app['config']['mailpreview.maximum_lifetime']
                )
            );
        });
    }
}
