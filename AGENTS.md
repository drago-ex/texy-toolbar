# Agent Guide

Use this file as package-specific guidance when working in this repository. Keep changes aligned with the existing public API, project conventions, and supported PHP range.

## Package Overview
- **Package**: `drago-ex/texy-toolbar` (Composer) and `drago-texy-toolbar` (npm, installed from `vendor/drago-ex/texy-toolbar` by the project's npm-loader)
- **Type**: library, PHP and JavaScript
- **Purpose**: everything around Texy in one package: the toolbar with a preview for a textarea marked with `data-texy-toolbar`, syntax highlighting of code blocks registered through Nette DI, and the copy button of code blocks.
- **PHP**: >=8.3 <9
- **Runtime dependencies**: nette/di (^3.2), texy/texy (^3.2); JavaScript has none (Bootstrap 5 classes only, own SVG icons)
- **License**: MIT

## Repository Layout
- `src/Texy/`: PHP, namespace `Drago\Texy` (PSR-4 `Drago\` => `src`).
- `src/assets/`: the npm package (see `package.json` exports): `texy-toolbar.js` (`.`), `copy-code.js` (`./copy-code`), `format.js`.
- `tests/`: Nette Tester (`*.phpt`) and `node --test` (`*.test.js`).
- `.github/`: CI workflows; keep their PHP and Node versions and commands consistent with Composer, npm and the package scripts.

## Key Parts
- `Highlighter::highlight()` matches one regular expression per language built from the token rules in `getRules()`; at the same position the first rule wins, so comments and strings come first. Text between tokens is only escaped. Colors are Bootstrap 5 classes in `Highlighter::Classes`.
- `CodeBlockHandler` is an invokable Texy `block` handler: `block/code` with a supported language is highlighted into `<pre class="{preClass}"><code>`, the rest goes to `$invocation->proceed()`.
- `DI\TexyExtension` registers `texy.highlighter` and `texy.codeBlockHandler` (autowired); config `preClass`. The project creates its own Texy (its safe settings belong to the project) and adds the handler, see project-static-page `PageContent`.
- `format.js`: pure function computing how a rule (`wrap`, `line`, `block`) changes the text and the selection.
- `texy-toolbar.js`: builds the toolbar, inserts with `execCommand('insertText')` so Ctrl+Z works, the preview POSTs `texy` (through `naja.makeRequest()` as a Naja extension, so the spinner shows; otherwise `fetch`) and prints the returned `html`; the default export is a Naja extension (forms in snippets), `initTexyToolbar()` works without Naja. The preview endpoint belongs to the project.
- Keep it simple on purpose (user decision): no nested languages (e.g. `<script>` inside HTML), no regex literals in JS, no custom stylesheet. Written from scratch; do not copy code from other highlighters or editors.

## Essential Commands

```bash
composer tester                 # tester tests -s -C
composer phpstan                # phpstan analyse
npm test                        # node --test
```

## Conventions & Rules

- Preserve backward compatibility unless the task explicitly permits a breaking change. Check callers and public signatures before changing behavior.
- Follow the existing PHP style: strict types, explicit parameter and return types, tabs for indentation, and single-quoted strings where practical. Match nearby code when a convention is unclear.
- Add or update focused tests for behavioral changes. Do not invent test commands; use only the scripts and configuration present here.
- Update README examples and Composer metadata when a public API or supported requirement changes.
