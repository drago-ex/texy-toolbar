<?php

declare(strict_types=1);

if (@!include __DIR__ . '/../vendor/autoload.php') {
	echo 'Install Nette Tester using `composer install`';
	exit(1);
}


Tester\Environment::setup();
Tester\Environment::setupFunctions();
date_default_timezone_set('Europe/Prague');


/** Temporary directory of the current test, for the compiled DI container. */
function getTempDir(): string
{
	$dir = __DIR__ . '/tmp/' . getmypid();
	@mkdir($dir, recursive: true); // @ - may exist
	Tester\Helpers::purge($dir);
	return $dir;
}
