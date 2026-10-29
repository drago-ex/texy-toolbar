<?php declare(strict_types=1);

/**
 * Test: Drago\Texy\DI\TexyExtension
 */

use Drago\Texy\TexyFormatter;
use Drago\Texy\TexyToolbar;
use Nette\DI\Compiler;
use Nette\DI\Container;
use Nette\DI\ContainerLoader;
use Nette\DI\Extensions\ExtensionsExtension;
use Tester\Assert;

require __DIR__ . '/../bootstrap.php';


test('Packages may register the extension each in its own config file', function () {
	$loader = new ContainerLoader(getTempDir(), autoRebuild: true);
	$class = $loader->load(function (Compiler $compiler): void {
		$compiler->addExtension('extensions', new ExtensionsExtension);
		$compiler->loadConfig(__DIR__ . '/files/page.neon');
		$compiler->loadConfig(__DIR__ . '/files/blog.neon');
	}, __FILE__);

	$container = new $class;
	assert($container instanceof Container);
	Assert::type(TexyToolbar::class, $container->getService('texy.toolbar'));

	// headingTop from the second file
	$formatter = $container->getByType(TexyFormatter::class);
	Assert::contains('<h3>Title</h3>', $formatter?->toHtml("Title\n====="));
});
