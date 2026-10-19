<?php

declare(strict_types=1);

namespace Drago\Texy\DI;

use Drago\Texy\CodeBlockHandler;
use Drago\Texy\Highlighter;
use Nette\DI\CompilerExtension;
use Nette\Schema\Expect;
use Nette\Schema\Schema;
use stdClass;


/**
 * Registers the Highlighter and the CodeBlockHandler as autowired services.
 *
 * extensions:
 *     texy: Drago\Texy\DI\TexyExtension
 *
 * texy:
 *     preClass: 'bg-body-tertiary p-3 rounded'
 *
 * @property-read stdClass $config
 */
final class TexyExtension extends CompilerExtension
{
	public function getConfigSchema(): Schema
	{
		return Expect::structure([
			'preClass' => Expect::string('bg-body-tertiary p-3 rounded'),
		]);
	}


	public function loadConfiguration(): void
	{
		$builder = $this->getContainerBuilder();

		$builder->addDefinition($this->prefix('highlighter'))
			->setFactory(Highlighter::class);

		$builder->addDefinition($this->prefix('codeBlockHandler'))
			->setFactory(CodeBlockHandler::class, [
				'preClass' => $this->config->preClass,
			]);
	}
}
