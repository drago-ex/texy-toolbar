/**
 * Computes how a toolbar rule changes the text of a textarea. Pure function, so the editing is tested without
 * a browser. Returns the range to replace, the text to insert and the selection after the change.
 *
 * Rules:
 * - { wrap: ['**', '**'] }    wraps the selection, without one puts the cursor between the marks
 * - { line: '- ' }            prefixes every selected line, '1) ' is numbered
 * - { block: ['/--', '\--'] } like wrap, but the marks stand on their own lines
 */
export function format(value, start, end, rule) {
	if (rule.line !== undefined) {
		return formatLines(value, start, end, rule.line);
	}

	let [before, after] = rule.wrap ?? rule.block;
	if (rule.block) {
		before = (start > 0 && value[start - 1] !== '\n' ? '\n' : '') + before + '\n';
		after = '\n' + after + (end < value.length && value[end] !== '\n' ? '\n' : '');
	}

	const selected = value.slice(start, end);
	const cursor = start + before.length;
	return {
		from: start,
		to: end,
		text: before + selected + after,
		selectionStart: cursor,
		selectionEnd: cursor + selected.length,
	};
}


function formatLines(value, start, end, prefix) {
	const from = value.lastIndexOf('\n', start - 1) + 1;
	// a selection ending right after a line break does not take the next line
	const to = end > start && value[end - 1] === '\n' ? end - 1 : end;
	const lineEnd = value.indexOf('\n', to);
	const original = value.slice(from, lineEnd === -1 ? value.length : lineEnd);
	const numbered = prefix === '1) ';
	const text = original.split('\n').map((line, i) => (numbered ? `${i + 1}) ` : prefix) + line).join('\n');

	// without a selection the cursor moves behind the prefix, a selection keeps all changed lines selected
	const cursor = start + text.length - original.length;
	return {
		from,
		to: from + original.length,
		text,
		selectionStart: start === end ? cursor : from,
		selectionEnd: start === end ? cursor : from + text.length,
	};
}
