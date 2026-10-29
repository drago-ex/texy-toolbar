<?php declare(strict_types=1);

if (@!include __DIR__ . '/../vendor/autoload.php') { // @ dependencies may not be installed
	echo 'Install Nette Tester using `composer install`';
	exit(1);
}


Tester\Environment::setup();
Tester\Environment::setupFunctions();
date_default_timezone_set('Europe/Prague');


function getTempDir(): string
{
	$dir = __DIR__ . '/tmp/' . getmypid();
	@mkdir($dir, 0o777, true); // @ directory may already exist
	return $dir;
}
