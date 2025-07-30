<?php declare(strict_types=1);

namespace Price2Performance\SendGrid\DI;

use Nette\DI\CompilerExtension;
use Nette\Mail\Mailer;
use Nette\Schema\Expect;
use Nette\Schema\Schema;
use Price2Performance\SendGrid\SendGridMailer;
use SendGrid;

class SendGridExtension extends CompilerExtension
{

    public function getConfigSchema(): Schema
	{
		return Expect::structure([
			'key' => Expect::string()->dynamic(),
			'options' => Expect::anyof(
				Expect::string(),
				Expect::array()
			)->nullable(),
		]);
	}

	public function loadConfiguration(): void
	{
		$builder = $this->getContainerBuilder();
		$config = $this->config;

        $originalMailer = $builder->getDefinitionByType(Mailer::class);
        $originalMailer->setAutowired(false);

		$sendgrid = $builder->addDefinition($this->prefix('sendgrid'))
			->setFactory(SendGrid::class, [
				$config->key,
				$config->options,
			]);

		$builder->addDefinition($this->prefix('mailer'))
			->setFactory(SendGridMailer::class, [
				$sendgrid
			]);
	}
}
