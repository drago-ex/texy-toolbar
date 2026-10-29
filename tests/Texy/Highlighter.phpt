<?php declare(strict_types=1);

/**
 * Test: Drago\Texy\Highlighter
 */

use Drago\Texy\Highlighter;
use Tester\Assert;

require __DIR__ . '/../bootstrap.php';


function span(string $type, string $html): string
{
	return '<span class="' . Highlighter::Classes[$type] . '">' . $html . '</span>';
}


test('PHP', function () {
	Assert::same(
		span('keyword', 'echo') . ' ' . span('variable', '$name') . ' . ' . span('string', '&#039;!&#039;')
		. '; ' . span('comment', '// greeting'),
		(new Highlighter)->highlight("echo \$name . '!'; // greeting", 'php'),
	);
	Assert::same(
		span('keyword', 'return') . ' ' . span('number', '42') . ';',
		(new Highlighter)->highlight('return 42;', 'PHP'),
	);
	Assert::same('#[Inject]', (new Highlighter)->highlight('#[Inject]', 'php'));
});


test('A keyword inside a string or a comment is not highlighted', function () {
	Assert::same(
		span('string', '&quot;if // else&quot;'),
		(new Highlighter)->highlight('"if // else"', 'php'),
	);
	Assert::same(span('comment', '/* if &#039;x&#039; */'), (new Highlighter)->highlight("/* if 'x' */", 'php'));
});


test('NEON', function () {
	Assert::same(
		span('attribute', 'debug') . ': ' . span('keyword', 'yes') . ' ' . span('comment', '# mode') . "\n"
		. span('attribute', 'url') . ': ' . span('variable', '%baseUrl%') . '/x#y',
		(new Highlighter)->highlight("debug: yes # mode\nurl: %baseUrl%/x#y", 'neon'),
	);
});


test('Latte and HTML', function () {
	Assert::same(
		span('tag', '&lt;a') . ' ' . span('attribute', 'href') . '=' . span('string', '&quot;/&quot;')
		. ' ' . span('attribute', 'n:if') . '=' . span('string', '&quot;$show&quot;') . span('tag', '&gt;')
		. span('macro', '{$title}') . span('tag', '&lt;/a') . span('tag', '&gt;') . ' ' . span('comment', '{* note *}'),
		(new Highlighter)->highlight('<a href="/" n:if="$show">{$title}</a> {* note *}', 'latte'),
	);
	Assert::same(
		span('tag', '&lt;p') . span('tag', '&gt;') . 'Don&#039;t' . span('tag', '&lt;/p') . span('tag', '&gt;'),
		(new Highlighter)->highlight("<p>Don't</p>", 'html'),
	);
});


test('SQL', function () {
	Assert::same(
		span('keyword', 'SELECT') . ' * ' . span('keyword', 'from') . ' ' . span('attribute', '`page`') . ' '
		. span('keyword', 'WHERE') . ' id = ' . span('number', '1') . ' ' . span('comment', '-- one'),
		(new Highlighter)->highlight('SELECT * from `page` WHERE id = 1 -- one', 'sql'),
	);
});


test('CSS', function () {
	Assert::same(
		span('tag', '.card') . ':hover { ' . span('attribute', 'color') . ': ' . span('number', '#fff') . '; '
		. span('attribute', 'margin') . ': ' . span('number', '1rem') . ' ' . span('keyword', '!important') . '; }',
		(new Highlighter)->highlight('.card:hover { color: #fff; margin: 1rem !important; }', 'css'),
	);
});


test('JavaScript', function () {
	Assert::same(
		span('keyword', 'const') . ' url = ' . span('string', '`/page/${id}`') . '; ' . span('comment', '// link'),
		(new Highlighter)->highlight('const url = `/page/${id}`; // link', 'javascript'),
	);
});


test('The code of an unknown language is only escaped', function () {
	$highlighter = new Highlighter;
	Assert::same('&lt;b&gt;if&lt;/b&gt;', $highlighter->highlight('<b>if</b>', 'ruby'));
	Assert::false($highlighter->isSupported('ruby'));
	Assert::true($highlighter->isSupported('HTML'));
});
