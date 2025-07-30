<?php declare(strict_types=1);

namespace Price2Performace\SendGrid\Tests;

require __DIR__ . '/../Bootstrap.php';

use Mockery;
use Price2Performance\SendGrid\SendGridMailer;
use SendGrid;
use Tester\Assert;
use Tester\TestCase;

class SendGridMailerTest extends TestCase
{

	/** @var \Nette\DI\Container */
	private $container;

	public function __construct(\Nette\DI\Container $container)
	{
		$this->container = $container;
	}

	public function testMessage()
	{
		$container = $this->container;

		$message = new \Nette\Mail\Message();
		$message->setFrom('from@example.com', 'First Last');
		$message->addTo('to@example.com', 'ToFirst ToLast');
		$message->addCc('cc@example.com', 'CcFirst CcLast');
		$message->addBcc('bcc@example.com', 'BccFirst BccLast');

		$message->setSubject('TESTING SUBJECT');
		$message->setBody('TESTING BODY');
		$message->setHtmlBody('<p>TESTING HTML <em>BODY</em>.</p>');
		$message->addAttachment('filename.txt', 'CONTENT OF THE ATTACHMENT');

		$response = Mockery::mock(\SendGrid\Response::class);
		$sendgrid = Mockery::mock(SendGrid::class);
		/** @var \SendGrid\Mail\Mail $messageToBeSent */
		$sendgrid->shouldReceive('send')
			->with(Mockery::capture($messageToBeSent))
			->andReturn($response);

		$mailer = new SendgridMailer($sendgrid);
		$mailer->send($message);

		Assert::type(SendgridMailer::class, $mailer);

		Assert::same('First Last', $messageToBeSent->getFrom()->getName());
		Assert::same('from@example.com', $messageToBeSent->getFrom()->getEmail());
		$to = $messageToBeSent->getPersonalizations()[0]->getTos()[0];
		Assert::same('to@example.com', $to->getEmail());
		Assert::same('ToFirst ToLast', $to->getName());
		$cc = $messageToBeSent->getPersonalizations()[0]->getCcs()[0];
		Assert::same('cc@example.com', $cc->getEmail());
		Assert::same('CcFirst CcLast', $cc->getName());
		$bcc = $messageToBeSent->getPersonalizations()[0]->getBccs()[0];
		Assert::same('bcc@example.com', $bcc->getEmail());
		Assert::same('BccFirst BccLast', $bcc->getName());

		Assert::same('TESTING SUBJECT', $messageToBeSent->getGlobalSubject()->getSubject());

		$textBody = $messageToBeSent->getContents()[0];
		Assert::same('text/plain', $textBody->getType());
		Assert::same('TESTING BODY', $textBody->getValue());

		$htmlBody = $messageToBeSent->getContents()[1];
		Assert::same('text/html', $htmlBody->getType());
		Assert::same('<p>TESTING HTML <em>BODY</em>.</p>', $htmlBody->getValue());

		Assert::count(1, $messageToBeSent->getAttachments());
		$attachement = $messageToBeSent->getAttachments()[0];
		Assert::same('filename.txt', $attachement->getFilename());
		Assert::same(base64_encode('CONTENT OF THE ATTACHMENT'), $attachement->getContent());

		Assert::same($response, $mailer->getLastResponse());
	}

}

$container = Bootstrap::bootForTests()->createContainer();
(new SendGridMailerTest($container))->run();