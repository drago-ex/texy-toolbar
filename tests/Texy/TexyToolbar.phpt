<?php declare(strict_types=1);

/**
 * Test: Drago\Texy\TexyToolbar
 */

use Drago\Texy\TexyFormatter;
use Drago\Texy\TexyToolbar;
use Nette\Http\Request;
use Nette\Http\UrlScript;
use Nette\Localization\Translator;
use Tester\Assert;

require __DIR__ . '/../bootstrap.php';


test('Options hold the preview URL and the labels, translated when there is a translator', function () {
	$request = new Request(new UrlScript('http://localhost/'));
	$options = (new TexyToolbar(new TexyFormatter, $request))->getOptions('/page?do=preview');
	Assert::same('/page?do=preview', $options['preview']);
	Assert::same(TexyToolbar::Labels, $options['labels']);

	$translator = new class implements Translator {
		public function translate(string|Stringable $message, mixed ...$parameters): string
		{
			return 'cs:' . $message;
		}
	};
	$options = (new TexyToolbar(new TexyFormatter, $request, $translator))->getOptions('/');
	Assert::same('cs:Bold', $options['labels']['bold']);
});


test('Basic options show only the basic buttons', function () {
	$request = new Request(new UrlScript('http://localhost/'));
	$options = (new TexyToolbar(new TexyFormatter, $request))->getBasicOptions('/');
	Assert::same(TexyToolbar::BasicButtons, $options['buttons']);
	Assert::same('/', $options['preview']);
});


test('The preview converts the POST field texy', function () {
	$request = new Request(
		new UrlScript('http://localhost/'),
		post: ['texy' => "**bold**\n\n[* https://example.com/a.png *]"],
		method: 'POST',
	);
	$toolbar = new TexyToolbar(new TexyFormatter, $request);
	Assert::contains('<strong>bold</strong>', $toolbar->getPreview()['html']);
	Assert::contains('<img', $toolbar->getPreview()['html']);
	Assert::notContains('<img', $toolbar->getBasicPreview()['html']);

	$request = new Request(new UrlScript('http://localhost/'), method: 'POST');
	Assert::same(['html' => ''], (new TexyToolbar(new TexyFormatter, $request))->getPreview());
});
