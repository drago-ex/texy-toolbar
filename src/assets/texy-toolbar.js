/**
 * Toolbar for writing Texy in a textarea marked with the data-texy-toolbar attribute. A button inserts
 * the marks of Texy around the selection or puts the cursor where the text belongs. The attribute may hold
 * JSON with the preview URL, translated labels and the shown buttons (all when missing):
 * {"preview": "/url", "labels": {"bold": "Tučně"}, "buttons": ["bold", "italic", "code"]}.
 * Only Bootstrap 5 classes and own icons.
 */
import { format } from './format.js';

export { format };

const icon = (paths) => '<svg width="16" height="16" viewBox="0 0 16 16" fill="none" stroke="currentColor"'
	+ ' stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">' + paths + '</svg>';

const buttons = [
	{ name: 'bold', key: 'b', content: '<span class="fw-bold">B</span>', rule: { wrap: ['**', '**'] } },
	{ name: 'italic', key: 'i', content: '<span class="fst-italic font-monospace">I</span>', rule: { wrap: ['*', '*'] } },
	{ name: 'heading', content: '<span class="fw-bold">H</span>', rule: { line: '### ' } },
	{ name: 'list', content: icon('<path d="M6 4h8M6 8h8M6 12h8"/><circle cx="2.5" cy="4" r=".5"/><circle cx="2.5" cy="8" r=".5"/>'
		+ '<circle cx="2.5" cy="12" r=".5"/>'), rule: { line: '- ' } },
	{ name: 'numbered', content: icon('<path d="M6 4h8M6 8h8M6 12h8M2 2.5h1v3M2 9.5h2l-2 2.5h2"/>'), rule: { line: '1) ' } },
	{ name: 'quote', content: icon('<path d="M3 12c0-3.5 1-5.5 3-7M9 12c0-3.5 1-5.5 3-7"/>'), rule: { line: '> ' } },
	{ name: 'link', content: icon('<path d="M6.5 9.5l3-3M7 4.5l1-1a2.5 2.5 0 0 1 3.5 3.5l-1 1M9 11.5l-1 1a2.5 2.5 0 0 1-3.5-3.5l1-1"/>'),
		rule: { wrap: ['"', '":https://'] } },
	{ name: 'image', content: icon('<rect x="1.5" y="2.5" width="13" height="11" rx="1.5"/><path d="M1.5 11l4-4 3 3 2-2 4 4"/>'
		+ '<circle cx="11" cy="5.5" r="1"/>'), rule: { wrap: ['[* ', ' *]'] } },
];

const defaultLabels = {
	bold: 'Bold',
	italic: 'Italic',
	heading: 'Heading',
	list: 'List',
	numbered: 'Numbered list',
	quote: 'Quote',
	link: 'Link',
	image: 'Image',
	code: 'Code',
	preview: 'Preview',
	edit: 'Edit',
	previewError: 'The preview could not be loaded.',
};

const languages = ['php', 'neon', 'latte', 'sql', 'css', 'js'];

// set when the toolbar runs as a Naja extension
let naja = null;


/** Inserts the change of a rule so that Ctrl+Z can undo it. */
function apply(textarea, rule) {
	const change = format(textarea.value, textarea.selectionStart, textarea.selectionEnd, rule);
	textarea.focus();
	textarea.setSelectionRange(change.from, change.to);
	if (!document.execCommand('insertText', false, change.text)) {
		textarea.setRangeText(change.text);
		textarea.dispatchEvent(new Event('input', { bubbles: true }));
	}
	textarea.setSelectionRange(change.selectionStart, change.selectionEnd);
}


function createToolbar(textarea) {
	const options = textarea.dataset.texyToolbar ? JSON.parse(textarea.dataset.texyToolbar) : {};
	const labels = { ...defaultLabels, ...options.labels };
	// names of the shown buttons, all by default; 'code' is the select of the code block
	const shown = (name) => !options.buttons || options.buttons.includes(name);
	const shownButtons = buttons.filter(({ name }) => shown(name));
	const controls = [];

	const toolbar = document.createElement('div');
	toolbar.className = 'd-flex flex-wrap align-items-center gap-1 mb-2';

	for (const { name, content, rule } of shownButtons) {
		const button = document.createElement('button');
		button.type = 'button';
		button.className = 'btn btn-sm btn-secondary lh-1';
		button.style.minWidth = '2rem';
		button.title = labels[name];
		button.setAttribute('aria-label', labels[name]);
		button.innerHTML = content;
		button.addEventListener('click', () => apply(textarea, rule));
		controls.push(button);
	}

	if (shown('code')) {
		const code = document.createElement('select');
		code.className = 'form-select form-select-sm w-auto';
		code.setAttribute('aria-label', labels.code);
		code.add(new Option(labels.code, ''));
		languages.forEach((language) => code.add(new Option(language, language)));
		code.addEventListener('change', () => {
			apply(textarea, { block: ['/--code ' + code.value, '\\--'] });
			code.value = '';
		});
		controls.push(code);
	}

	toolbar.append(...controls);

	textarea.addEventListener('keydown', (event) => {
		const button = shownButtons.find(({ key }) => key && key === event.key.toLowerCase());
		if (button && (event.ctrlKey || event.metaKey) && !event.shiftKey && !event.altKey) {
			event.preventDefault();
			apply(textarea, button.rule);
		}
	});

	if (options.preview) {
		toolbar.append(createPreview(textarea, options.preview, labels, controls));
	}

	textarea.before(toolbar);
}


/** The preview button swaps the textarea for the HTML that the server makes of the text. */
function createPreview(textarea, url, labels, controls) {
	const preview = document.createElement('div');
	preview.className = 'form-control overflow-auto d-none';

	const button = document.createElement('button');
	button.type = 'button';
	button.className = 'btn btn-sm btn-primary ms-auto';
	button.textContent = labels.preview;
	button.addEventListener('click', async () => {
		const showing = !preview.classList.contains('d-none');
		if (!showing) {
			// a second click while loading would toggle the preview before it arrives
			button.disabled = true;
			preview.style.minHeight = textarea.offsetHeight + 'px';
			preview.innerHTML = await loadPreview(url, textarea.value, labels);
			button.disabled = false;
		}

		preview.classList.toggle('d-none', showing);
		textarea.classList.toggle('d-none', !showing);
		controls.forEach((control) => control.disabled = !showing);
		button.textContent = showing ? labels.preview : labels.edit;
	});

	textarea.after(preview);
	return button;
}


async function loadPreview(url, text, labels) {
	const body = new FormData();
	body.append('texy', text);
	try {
		// through Naja the request shows its spinner, unique: false keeps other requests running
		if (naja) {
			return (await naja.makeRequest('POST', url, body, { history: false, unique: false })).html;
		}

		const response = await fetch(url, { method: 'POST', body, headers: { 'X-Requested-With': 'XMLHttpRequest' } });
		if (!response.ok) {
			throw new Error(response.statusText);
		}

		// the server returns HTML that Texy has already made safe
		return (await response.json()).html;
	} catch {
		const error = document.createElement('p');
		error.className = 'text-danger mb-0';
		error.textContent = labels.previewError;
		return error.outerHTML;
	}
}


export function initTexyToolbar(root = document) {
	for (const textarea of root.querySelectorAll('textarea[data-texy-toolbar]')) {
		if (!textarea.texyToolbar) {
			textarea.texyToolbar = true;
			createToolbar(textarea);
		}
	}
}


/** Naja extension, so the toolbar also appears in snippets, e.g. a form in an offcanvas. */
export default class TexyToolbar {
	initialize(instance) {
		naja = instance;
		initTexyToolbar();
		naja.snippetHandler.addEventListener('afterUpdate', (event) => initTexyToolbar(event.detail.snippet));
	}
}
