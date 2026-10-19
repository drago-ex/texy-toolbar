<?php

declare(strict_types=1);

namespace Drago\Texy\DI;

use Drago\Texy\Highlighter;
use Drago\Texy\TexyFormatter;
use Drago\Texy\TexyToolbar;
use Nette\DI\CompilerExtension;
use Nette\Schema\Expect;
use Nette\Schema\Schema;


/**
 * Registers the Highlighter, TexyFormatter and TexyToolbar. Several packages may register the extension
 * under the same name, the configuration is merged.
 */
class TexyExtension extends CompilerExtension
{
	public function getConfigSchema(): Schema
	{
		return Expect::structure([
			'headingTop' => Expect::int(2)->min(1)->max(6),
		]);
	}


	public function loadConfiguration(): void
	{
		$builder = $this->getContainerBuilder();
		/** @var object{headingTop: int} $config */
		$config = $this->config;

		$builder->addDefinition($this->prefix('highlighter'))
			->setFactory(Highlighter::class);

		$builder->addDefinition($this->prefix('formatter'))
			->setFactory(TexyFormatter::class, ['headingTop' => $config->headingTop]);

		$builder->addDefinition($this->prefix('toolbar'))
			->setFactory(TexyToolbar::class);
	}
}
