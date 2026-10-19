<?php

/**
 * Test: Drago\Texy\DI\TexyExtension
 */

declare(strict_types=1);

use Drago\Texy\CodeBlockHandler;
use Drago\Texy\DI\TexyExtension;
use Drago\Texy\Highlighter;
use Nette\DI\Compiler;
use Nette\DI\Container;
use Nette\DI\ContainerLoader;
use Tester\Assert;
use Texy\Texy;

require __DIR__ . '/../bootstrap.php';


/** @param array<string, mixed> $config */
function createContainer(array $config = []): Container
{
	$loader = new ContainerLoader(getTempDir(), autoRebuild: true);
	$class = $loader->load(function (Compiler $compiler) use ($config): void {
		$compiler->addExtension('texy', new TexyExtension);
		$compiler->addConfig(['texy' => $config]);
	}, serialize($config));

	/** @var Container */
	return new $class;
}


test('The services are autowired', function () {
	$container = createContainer();
	$handler = $container->getByType(CodeBlockHandler::class);
	Assert::type(Highlighter::class, $container->getByType(Highlighter::class));
	Assert::same($handler, $container->getService('texy.codeBlockHandler'));

	$texy = new Texy;
	$texy->addHandler('block', $handler);
	Assert::contains('<pre class="bg-body-tertiary p-3 rounded">', $texy->process("/--code php\necho 1;\n\\--"));
});


test('The class of the pre element is configurable', function () {
	$texy = new Texy;
	$texy->addHandler('block', createContainer(['preClass' => 'code-block'])->getByType(CodeBlockHandler::class));
	Assert::contains('<pre class="code-block">', $texy->process("/--code php\necho 1;\n\\--"));
});
