# MailJet Driver for Laravel 5.5 and above


This package introduces a new `mailjet` mail driver for Laravel that when selected will
send email via MailJet SEND API

## Installation

Begin by installing the package through Composer. Run the following command in your terminal:

```bash
composer require himanverma/mailjet-laravel-driver
```


Finally, change `MAIL_DRIVER` to `mailjet` in your `.env` file:

```
MAIL_DRIVER=mailjet


```

## Package Configurations
in services.php 

add

```

'mailjet' => [
        "username" => "c1029eaf1cxxxxxxxxxxxfc3ba578",
        "secret" => "c7c85a1254xxxxxxxxxxxxxxf8081f"
    ]

```