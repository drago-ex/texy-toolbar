# Drago Texy Toolbar

[Texy](https://texy.nette.org) for Nette: a toolbar for writing Texy in a textarea, syntax highlighting of code
blocks and a copy button. Only Bootstrap 5 classes, no extra styles, the colors follow the light and dark theme.

[![License: MIT](https://img.shields.io/badge/License-MIT-yellow.svg)](https://github.com/drago-ex/texy-toolbar/blob/main/license)
[![PHP version](https://badge.fury.io/ph/drago-ex%2Ftexy-toolbar.svg)](https://badge.fury.io/ph/drago-ex%2Ftexy-toolbar)
[![Tests](https://github.com/drago-ex/texy-toolbar/actions/workflows/tests.yml/badge.svg)](https://github.com/drago-ex/texy-toolbar/actions/workflows/tests.yml)
[![Coding Style](https://github.com/drago-ex/texy-toolbar/actions/workflows/coding-style.yml/badge.svg)](https://github.com/drago-ex/texy-toolbar/actions/workflows/coding-style.yml)

## Requirements

- PHP >= 8.3, < 9
- Composer, npm
- Bootstrap 5.3

## Installation

```
composer require drago-ex/texy-toolbar
```

Add the Composer package as a local npm dependency and run `npm install`:

```json
{
	"dependencies": {
		"drago-texy-toolbar": "file:vendor/drago-ex/texy-toolbar"
	}
}
```

## Highlighting code blocks

Register the extension; it adds the `Highlighter` and the `CodeBlockHandler` as autowired services:

```neon
extensions:
	texy: Drago\Texy\DI\TexyExtension

# optional, the class of the <pre> element
texy:
	preClass: 'bg-body-tertiary p-3 rounded'
```

Add the handler to your Texy, code blocks with a language (`/--code php`) are then highlighted:

```php
use Drago\Texy\CodeBlockHandler;
use Texy\Texy;

class Content
{
	public function __construct(
		private readonly CodeBlockHandler $codeBlockHandler,
	) {
	}


	public function toHtml(string $text): string
	{
		$texy = new Texy;
		$texy->addHandler('block', $this->codeBlockHandler);
		return $texy->process($text);
	}
}
```

Languages: `php`, `neon`, `latte` (also `html`), `sql`, `css`, `js` (also `javascript`). The code is always
escaped, blocks of an unknown language are left to Texy.

The `Highlighter` works on its own too:

```php
$html = $highlighter->highlight('echo $name;', 'php');
// <span class="text-primary">echo</span> <span class="text-warning-emphasis">$name</span>;
```

`isSupported()` tells whether a language is known, the classes of the token types are in `Highlighter::Classes`.

## Toolbar

Bold, italic, heading, lists, quote, link, image and code block. A button wraps the selected text, or puts the
cursor where the text belongs. An optional preview shows the HTML made by the server.

Register the Naja extension, so the toolbar also appears in snippets (without Naja call `initTexyToolbar()`):

```js
import TexyToolbar from 'drago-texy-toolbar';

naja.registerExtension(new TexyToolbar);
```

Mark the textarea. The attribute may hold JSON with the preview URL and translated labels:

```php
$form->addTextArea('content', 'Content')
	->setHtmlAttribute('data-texy-toolbar', [
		'preview' => $this->link('preview!'),
		'labels' => ['bold' => 'Tučně', 'preview' => 'Náhled'],
	]);
```

The preview sends the text as the POST field `texy` and expects JSON `{"html": "..."}`; the HTML is printed
as it is, so it must be safe (e.g. Texy in safe mode). Ctrl+B and Ctrl+I work too.

## Copy button

A small transparent button in the top right corner of a code block copies the code; it appears when the mouse
is over the block.

```js
import { initCopyCode } from 'drago-texy-toolbar/copy-code';

document.addEventListener('DOMContentLoaded', () => initCopyCode({ label: 'Copy' }));
```

By default it is added to every `pre > code`; pass `selector` or `root` to limit it. Copying to the clipboard
works on HTTPS and localhost.
