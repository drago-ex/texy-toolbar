# Agent Guide

Use this file as package-specific guidance when working in this repository. Keep changes aligned with the existing public API, project conventions, and supported PHP range.

## Package Overview
- **Package**: `drago-ex/texy-toolbar` (Composer) and `drago-texy-toolbar` (npm, installed from `vendor/drago-ex/texy-toolbar` by the project's npm-loader)
- **Type**: library
- **Purpose**: everything about Texy in one place, so packages (static pages, blog, comments…) only inject services: safe Texy to HTML with highlighted code blocks, the toolbar with a preview for a textarea marked with `data-texy-toolbar`, and the copy button of code blocks. The former `drago-ex/highlighter` was merged here (2026-10-19).
- **PHP**: >=8.3 <9
- **Runtime dependencies**: texy/texy (^3.2), nette/di (^3.2), nette/http (^3.3), nette/utils (^4.0), nette/schema (^1.2); JS has none (Bootstrap 5 classes only, own SVG icons)
- **License**: MIT

## Key Parts
- `src/Texy/DI/TexyExtension.php`: registers `Highlighter`, `TexyFormatter` (config `headingTop`, default 2) and `TexyToolbar`. Resource packages register it in their own neon under the name `texy`; the same entry in several files merges, so do not rename it.
- `src/Texy/TexyFormatter.php`: `toHtml()` with safe settings (`Configurator::$safeTags`, no classes/styles, http(s)/mailto links, http(s) images), code blocks highlighted by `Highlighter`; `createTexy()` is public so a package can restrict it further. `toBasicHtml()`/`createBasicTexy()` for texts of visitors (blog comments, 2026-10-19): no HTML tags, images, headings, tables, horizontal lines or link definitions, links `rel="nofollow"`, `mergeLines = false` (Enter makes `<br>`).
- `src/Texy/TexyToolbar.php`: `getOptions($previewUrl)` for the `data-texy-toolbar` attribute (labels translated by an optional `Nette\Localization\Translator`), `getPreview()` reads the POST field `texy` and returns `['html' => ...]` for `sendJson()`; `getBasicOptions()` adds `buttons` = `BasicButtons` and `getBasicPreview()` uses `toBasicHtml()`. The preview signal stays in the control that owns the form. The JS option `buttons` lists the shown buttons (`code` is the language select), all when missing.
- `src/Texy/Translate/cs.neon`: Czech labels, found by drago-ex/translator through `extra.drago-translator`.
- `src/Texy/Highlighter.php`: one regular expression per language built from `getRules()`; at the same position the first rule wins, so comments and strings come first. Keep it simple on purpose (user decision): no nested languages, no regex literals in JS, colors are Bootstrap 5 classes in `Highlighter::Classes`.
- `src/assets/format.js`: pure function computing how a rule (`wrap`, `line`, `block`) changes the text and the selection; tested in `tests/format.test.js` with `node --test`.
- `src/assets/texy-toolbar.js`: builds the toolbar, inserts with `execCommand('insertText')` so Ctrl+Z works, the preview POSTs `texy` (through `naja.makeRequest()` as a Naja extension, so the spinner shows; otherwise `fetch`); the default export is a Naja extension, `initTexyToolbar()` works without Naja.
- `src/assets/copy-code.js`: npm subpath `drago-texy-toolbar/copy-code`, the copy button of `pre > code`.
- Written from scratch; do not copy code from other editors or highlighters.

## Essential Commands

```bash
composer tester                 # tester tests -s
composer phpstan                # phpstan analyse (level max)
npm test                        # node --test
```

## Conventions & Rules

- Preserve backward compatibility unless the task explicitly permits a breaking change. Check callers and public signatures before changing behavior.
- Follow the existing PHP style: strict types, explicit parameter and return types, tabs for indentation, and single-quoted strings where practical.
- Add or update focused tests for behavioral changes.
- Update README examples and Composer metadata when a public API or supported requirement changes.
