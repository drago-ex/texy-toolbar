# Drago Texy Toolbar

Everything for writing [Texy](https://texy.nette.org) in a Nette application: safe HTML with highlighted code
blocks, a toolbar with a preview for the textarea and a copy button for code blocks. Only Bootstrap 5 classes,
no extra styles.

[![License: MIT](https://img.shields.io/badge/License-MIT-yellow.svg)](https://github.com/drago-ex/texy-toolbar/blob/main/license)
[![PHP version](https://badge.fury.io/ph/drago-ex%2Ftexy-toolbar.svg)](https://badge.fury.io/ph/drago-ex%2Ftexy-toolbar)
[![Tests](https://github.com/drago-ex/texy-toolbar/actions/workflows/tests.yml/badge.svg)](https://github.com/drago-ex/texy-toolbar/actions/workflows/tests.yml)
[![Coding Style](https://github.com/drago-ex/texy-toolbar/actions/workflows/coding-style.yml/badge.svg)](https://github.com/drago-ex/texy-toolbar/actions/workflows/coding-style.yml)

## Requirements

- PHP >= 8.3, < 9
- Nette Framework 3.2
- Composer, npm
- Bootstrap 5.3

## Installation

```
composer require drago-ex/texy-toolbar
```

```neon
extensions:
	texy: Drago\Texy\DI\TexyExtension

# optional, the level of the first heading (default 2, as the page title is the <h1>)
texy:
	headingTop: 2
```

Several packages may register the extension under the same name in their own config files.

## Texy to HTML

`Drago\Texy\TexyFormatter` converts the text to HTML that can be printed without escaping: raw HTML is limited to
safe tags, links and images to safe URLs. Code blocks with a language (`/--code php`, also html, latte, css, js,
sql, neon) are highlighted.

```php
$this->template->content = $this->texyFormatter->toHtml($page->content);
```

`createTexy()` gives the configured Texy for a text that needs less (e.g. comments without images).
`Drago\Texy\Highlighter` can also be used alone: `$highlighter->highlight($code, 'php')`.

## Toolbar

Add the Composer package as a local npm dependency and run `npm install`:

```json
{
	"dependencies": {
		"drago-texy-toolbar": "file:vendor/drago-ex/texy-toolbar"
	}
}
```

Register the Naja extension, so the toolbar also appears in snippets (without Naja call `initTexyToolbar()`):

```js
import TexyToolbar from 'drago-texy-toolbar';

naja.registerExtension(new TexyToolbar);
```

Mark the textarea with the options of `Drago\Texy\TexyToolbar` (the preview URL and labels translated by the
application's translator) and answer the preview:

```php
$form->addTextArea('content', 'Content')
	->setHtmlAttribute('data-texy-toolbar', $this->texyToolbar->getOptions($this->link('preview!')));

#[Requires(methods: 'POST', ajax: true)]
public function handlePreview(): void
{
	$this->getPresenter()->sendJson($this->texyToolbar->getPreview());
}
```

Ctrl+B and Ctrl+I work too. The Czech labels come with the package for
[drago-ex/translator](https://github.com/drago-ex/translator).

## Copy button

A small button in the top right corner of a code block copies the code; it appears when the mouse is over the
block. Copying to the clipboard works on HTTPS and localhost.

```js
import { initCopyCode } from 'drago-texy-toolbar/copy-code';

document.addEventListener('DOMContentLoaded', () => initCopyCode({ label: 'Copy' }));
```

By default it is added to every `pre > code`; pass `selector` or `root` to limit it.
