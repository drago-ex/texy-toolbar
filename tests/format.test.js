import assert from 'node:assert/strict';
import { test } from 'node:test';
import { format } from '../src/assets/format.js';


/** Applies the change like the toolbar does and marks the selection with [ and ]. */
function run(value, rule) {
	const start = value.indexOf('[');
	const end = value.indexOf(']') - 1;
	value = value.replace('[', '').replace(']', '');
	const change = format(value, start, end, rule);
	const result = value.slice(0, change.from) + change.text + value.slice(change.to);
	return result.slice(0, change.selectionStart) + '[' + result.slice(change.selectionStart, change.selectionEnd)
		+ ']' + result.slice(change.selectionEnd);
}


test('wrap puts the cursor between the marks', () => {
	assert.equal(run('Hello []', { wrap: ['**', '**'] }), 'Hello **[]**');
});


test('wrap keeps the wrapped text selected', () => {
	assert.equal(run('Hello [world]!', { wrap: ['**', '**'] }), 'Hello **[world]**!');
	assert.equal(run('[Nette]', { wrap: ['"', '":https://'] }), '"[Nette]":https://');
});


test('line prefixes the line of the cursor', () => {
	assert.equal(run('first\nsec[]ond', { line: '- ' }), 'first\n- sec[]ond');
});


test('line prefixes every selected line', () => {
	assert.equal(run('[one\ntwo\nthree]', { line: '> ' }), '[> one\n> two\n> three]');
});


test('line numbers the lines of a numbered list', () => {
	assert.equal(run('[one\ntwo]', { line: '1) ' }), '[1) one\n2) two]');
});


test('line ignores the next line after a selection ending with a line break', () => {
	assert.equal(run('[one\n]two', { line: '- ' }), '[- one]\ntwo');
});


test('block puts its marks on their own lines', () => {
	assert.equal(run('Text[]', { block: ['/--code php', '\\--'] }), 'Text\n/--code php\n[]\n\\--');
	assert.equal(run('[echo 1;]\nnext', { block: ['/--code php', '\\--'] }), '/--code php\n[echo 1;]\n\\--\nnext');
	assert.equal(run('a [b] c', { block: ['/--', '\\--'] }), 'a \n/--\n[b]\n\\--\n c');
});
