<?php declare(strict_types=1);

namespace Price2Performance\SendGrid;

use Nette\Mail\Mailer;
use Nette\Mail\Message;
use Nette\SmartObject;
use SendGrid;
use SendGrid\Mail\Mail;
use SendGrid\Response;

class SendGridMailer implements Mailer
{
    use SmartObject;

    /** @var SendGrid */
    private $sendgrid;

    /** @var SendGrid\Response|null */
    private $lastResponse;

    public function __construct(SendGrid $sendgrid)
    {
        $this->sendgrid = $sendgrid;
    }

	/**
	 * @param Message $message
	 * @throws SendGrid\Mail\TypeException
	 */
    public function send(Message $message): void
    {
        $email = new Mail();

        $from = $message->getFrom();
        reset($from);
        $key = key($from);

        $email->setFrom($key, $from[$key]);
        $email->setSubject($message->getSubject());
        $email->addContent('text/plain', $message->getBody());
        $email->addContent('text/html', $message->getHtmlBody());

        foreach ($message->getAttachments() as $attachement) {
            $header = $attachement->getHeader('Content-Disposition');
            preg_match('/filename\=\"(.*)\"/', $header, $result);
            $originalFileName = $result[1];

            $email->addAttachment($attachement->getBody(), NULL, $originalFileName);
        }

        foreach ((array) $message->getHeader('To') as $recipient => $name) {
            $email->addTo($recipient, $name);
        }

        foreach ((array) $message->getHeader('Cc') as $recipient => $name) {
            $email->addCc($recipient, $name);
        }

        foreach ((array) $message->getHeader('Bcc') as $recipient => $name) {
            $email->addBcc($recipient, $name);
        }

        $this->lastResponse = $this->sendgrid->send($email);
    }

    public function getLastResponse(): ?Response
	{
		return $this->lastResponse;
	}

}
