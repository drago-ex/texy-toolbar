<?php

/**
 * Test: Drago\Texy\CodeBlockHandler
 */

declare(strict_types=1);

use Drago\Texy\CodeBlockHandler;
use Drago\Texy\Highlighter;
use Tester\Assert;
use Tester\DomQuery;
use Texy\Texy;

require __DIR__ . '/../bootstrap.php';


function process(string $text, ?CodeBlockHandler $handler = null): string
{
	$texy = new Texy;
	$texy->addHandler('block', $handler ?? new CodeBlockHandler(new Highlighter));
	return $texy->process($text);
}


test('A code block with a known language is highlighted and escaped', function () {
	$dom = DomQuery::fromHtml(process("/--code php\necho '<b>';\n\\--"));
	Assert::true($dom->has('pre.bg-body-tertiary > code > span.text-primary'));
	Assert::same('echo', (string) $dom->find('span.text-primary')[0]);
	Assert::same("'<b>'", (string) $dom->find('span.text-success')[0]);
	Assert::false($dom->has('b'));
});


test('A code block with an unknown language is left to Texy, still escaped', function () {
	$html = process("/--code ruby\nputs '<b>'\n\\--");
	Assert::contains('&lt;b&gt;', $html);
	Assert::notContains('<span', $html);
});


test('Other blocks are left to Texy', function () {
	Assert::contains('<div', process("/--div\nText\n\\--"));
});


test('The class of the pre element can be changed', function () {
	$html = process("/--code js\nlet a;\n\\--", new CodeBlockHandler(new Highlighter, 'code-block'));
	Assert::true(DomQuery::fromHtml($html)->has('pre.code-block > code'));
});
