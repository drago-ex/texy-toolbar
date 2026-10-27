# Drago Texy Toolbar

Vše pro psaní v [Texy](https://texy.nette.org) v aplikaci na Nette: bezpečné HTML se zvýrazněnými bloky kódu,
lišta s náhledem pro textové pole a tlačítko pro kopírování bloků kódu. Jen třídy Bootstrapu 5,
žádné styly navíc.

[![License: MIT](https://img.shields.io/badge/License-MIT-yellow.svg)](https://github.com/drago-ex/texy-toolbar/blob/main/license)
[![PHP version](https://badge.fury.io/ph/drago-ex%2Ftexy-toolbar.svg)](https://badge.fury.io/ph/drago-ex%2Ftexy-toolbar)
[![Tests](https://github.com/drago-ex/texy-toolbar/actions/workflows/tests.yml/badge.svg)](https://github.com/drago-ex/texy-toolbar/actions/workflows/tests.yml)
[![Coding Style](https://github.com/drago-ex/texy-toolbar/actions/workflows/coding-style.yml/badge.svg)](https://github.com/drago-ex/texy-toolbar/actions/workflows/coding-style.yml)

## Požadavky

- PHP >= 8.3, < 9
- Nette Framework 3.2
- Composer, npm
- Bootstrap 5.3

## Instalace

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

Rozšíření může pod stejným názvem zaregistrovat více balíčků ve svých konfiguračních souborech.

## Texy na HTML

`Drago\Texy\TexyFormatter` převede text na HTML, které lze vypsat bez escapování: přímé HTML je omezené na
bezpečné značky, odkazy a obrázky na bezpečné URL. Bloky kódu s jazykem (`/--code php`, také html, latte, css, js,
sql, neon) se zvýrazní.

```php
$this->template->content = $this->texyFormatter->toHtml($page->content);
```

Pro texty návštěvníků, např. komentáře, `toBasicHtml()` povolí jen tučné písmo, kurzívu, odkazy (s `rel="nofollow"`),
seznamy, citace a kód a zachová zalomení řádků; žádné obrázky, nadpisy, tabulky ani HTML značky.

`createTexy()` a `createBasicTexy()` vrátí nastavené Texy pro text, který potřebuje něco jiného.
`Drago\Texy\Highlighter` lze použít i samostatně: `$highlighter->highlight($code, 'php')`.

## Lišta

Přidejte balíček z Composeru jako lokální závislost npm a spusťte `npm install`:

```json
{
	"dependencies": {
		"drago-texy-toolbar": "file:vendor/drago-ex/texy-toolbar"
	}
}
```

Zaregistrujte rozšíření Naja, aby se lišta objevila i ve snippetech (bez Naja zavolejte `initTexyToolbar()`):

```js
import TexyToolbar from 'drago-texy-toolbar';

naja.registerExtension(new TexyToolbar);
```

Označte textové pole volbami z `Drago\Texy\TexyToolbar` (URL náhledu a popisky přeložené
překladačem aplikace) a odpovězte na náhled:

```php
$form->addTextArea('content', 'Content')
	->setHtmlAttribute('data-texy-toolbar', $this->texyToolbar->getOptions($this->link('preview!')));

#[Requires(methods: 'POST', ajax: true)]
public function handlePreview(): void
{
	$this->getPresenter()->sendJson($this->texyToolbar->getPreview());
}
```

Pro základní Texy použijte `getBasicOptions()` a `getBasicPreview()`: lišta pak ukáže jen jeho tlačítka.
Libovolná tlačítka lze vybrat klíčem `buttons` ve volbách (`bold`, `italic`, `heading`, `list`, `numbered`,
`quote`, `link`, `image`, `code`).

Fungují i Ctrl+B a Ctrl+I. České popisky jsou součástí balíčku pro
[drago-ex/translator](https://github.com/drago-ex/translator).

## Tlačítko pro kopírování

Malé tlačítko v pravém horním rohu bloku kódu zkopíruje kód; objeví se při najetí myší na
blok. Kopírování do schránky funguje na HTTPS a na localhostu.

```js
import { initCopyCode } from 'drago-texy-toolbar/copy-code';

document.addEventListener('DOMContentLoaded', () => initCopyCode({ label: 'Copy' }));
```

Ve výchozím stavu se přidá ke každému `pre > code`; pro omezení předejte `selector` nebo `root`.
