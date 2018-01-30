<?php

namespace MailjetLaravelDriver;

use Illuminate\Contracts\Http\Kernel;
use Illuminate\Support\ServiceProvider;

class MailJetServiceProvider extends ServiceProvider
{
    /**
     * Perform post-registration booting of services.
     *
     * @return void
     */
    public function boot()
    {
        if ($this->app['config']['mail.driver'] != 'mailjet') {
            return;
        }
    }

    /**
     * Register any package services.
     *
     * @return void
     */
    public function register()
    {
        $this->app->register(MailProvider::class);
    }

}
