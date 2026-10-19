<?php

/**
 * Test: Drago\Texy\TexyFormatter
 */

declare(strict_types=1);

use Drago\Texy\TexyFormatter;
use Tester\Assert;
use Tester\DomQuery;

require __DIR__ . '/../bootstrap.php';


test('Empty text gives no HTML', function () {
	$formatter = new TexyFormatter;
	Assert::same('', $formatter->toHtml(null));
	Assert::same('', $formatter->toHtml("  \n "));
});


test('Headings start at h2 by default, the level can be changed', function () {
	Assert::contains('<h2>About us</h2>', (new TexyFormatter)->toHtml("About us\n========"));
	Assert::contains('<h3>About us</h3>', (new TexyFormatter(headingTop: 3))->toHtml("About us\n========"));
});


test('Scripts, event handlers and unsafe URLs do not get into the HTML', function () {
	$html = (new TexyFormatter)->toHtml(
		"<script>alert(1)</script>\n\n"
		. "<b onclick=\"alert(1)\">bold</b>\n\n"
		. "\"link\":javascript:alert(1)\n\n"
		. '[* javascript:alert(1) *]',
	);

	// an unsafe link or image stays only as plain text
	Assert::notContains('<script', $html);
	Assert::notContains('onclick', $html);
	Assert::notContains('href="javascript:', $html);
	Assert::notContains('<img', $html);
	Assert::contains('<b>bold</b>', $html);
});


test('Links and images with safe URLs stay', function () {
	$html = (new TexyFormatter)->toHtml("\"Nette\":https://nette.org\n\n[* https://example.com/a.png *]");
	Assert::contains('<a href="https://nette.org">Nette</a>', $html);
	Assert::contains('<img src="https://example.com/a.png"', $html);
});


test('A code block with a known language is highlighted and escaped', function () {
	$html = (new TexyFormatter)->toHtml("/--code php\necho '<b>';\n\\--");
	$dom = DomQuery::fromHtml($html);
	Assert::true($dom->has('pre.bg-body-tertiary > code > span.text-primary'));
	Assert::same('echo', (string) $dom->find('span.text-primary')[0]);
	Assert::same("'<b>'", (string) $dom->find('span.text-success')[0]);
	Assert::false($dom->has('b'));
});


test('A code block with an unknown language is left to Texy, still escaped', function () {
	$html = (new TexyFormatter)->toHtml("/--code ruby\nputs '<b>'\n\\--");
	Assert::contains('&lt;b&gt;', $html);
	Assert::notContains('<span', $html);
});


test('The created Texy can be changed for a text that needs less', function () {
	$texy = (new TexyFormatter)->createTexy();
	$texy->allowed['image'] = false;
	Assert::notContains('<img', $texy->process('[* https://example.com/a.png *]'));
});
