# sendgrid-nette
Sendgrid integration for Nette.

[![Github badge](https://github.com/price2performance/sendgrid-nette/workflows/build/badge.svg)](https://github.com/price2performance/sendgrid-nette/actions) [![Build Status](https://travis-ci.org/price2performance/sendgrid-nette.svg?branch=master)](https://travis-ci.org/price2performance/sendgrid-nette) [![Coverage Status](https://coveralls.io/repos/price2performance/sendgrid-nette/badge.svg)](https://coveralls.io/r/price2performance/sendgrid-nette)    

## Install
```
composer require price2performance/sendgrid-nette
```

## Configuration
In config add:

```
extension:
    sendgrid: Price2Performance\SendGrid\DI\SendGridExtension

sendgrid:
    key: 'SECRET_KEY'
```

## Usage
To make any API call to SendGrid, just inject the SendGrid class to your presenter:

```php
    /** @var SendGrid @inject */
    public $sendgrid;
	
    public function actionDefault()
    {
        // CALL suppression/bounces
        try {
            $response = $this->sendgrid->client->suppression()->bounces()->get();
            print $response->statusCode() . "\n";
            print_r($response->headers());
            print $response->body() . "\n";
        } catch (Exception $e) {
            echo 'Caught exception: '.  $e->getMessage(). "\n";
        }
    }
	
```

To send an email via `SendGrid`, just inject `Price2Performance\SendGrid\SendGridMailer` to your presenter:

```php
    /** @var \Price2Performance\SendGrid\SendGridMailer @inject */
    public $mailer;
	
    protected function sendMail() {
        
        $message = new \Nette\Mail\Message();
        $message->addFrom('sender@example.com', 'Sender Name');
        $message->addTo('example@example.com');
        $message->setSubject('TEST SUBJECT');
        $message->setBody('TEST BODY');
        
        $this->mailer->send($message);
        
        /** @var \SendGrid\Response $response */
        $response = $this->mailer->getLastResponse();   // optional, for error logging
    }
	
```

Calling `getLastResponse()` on `SendGridMailer` gets you `SendGrid\Response` of the last `send()` call. You can use it to log errors.

## Versions

| Version |    Nette     | SendGrid API |PHP
|---------|:------------:|:------------:|:---:|
| 2.0     |     ^3.0     |     ^7.4     |7.1 - 8.3
| 2.1     | ^3.0 or ^4.0 |     ^8.1     |7.1 - 8.4
