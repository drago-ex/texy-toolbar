/**
 * Adds a button that copies the code of a block. The button sits in the top right corner of the block
 * and appears when the mouse is over it (on touch screens it is always shown). Only Bootstrap 5 classes.
 */
const copyIcon = '<svg width="16" height="16" viewBox="0 0 16 16" fill="none" stroke="currentColor" stroke-width="1.5" aria-hidden="true">'
	+ '<rect x="5.5" y="5.5" width="8" height="9" rx="1.5"/><path d="M10.5 3.5v-1a1 1 0 0 0-1-1h-6a1 1 0 0 0-1 1v8a1 1 0 0 0 1 1h1"/></svg>';

const doneIcon = '<svg width="16" height="16" viewBox="0 0 16 16" fill="none" stroke="currentColor" stroke-width="1.5" aria-hidden="true">'
	+ '<path d="M3 8.5l3 3 7-7"/></svg>';


export function initCopyCode({ root = document, selector = 'pre > code', label = 'Copy' } = {}) {
	const alwaysVisible = window.matchMedia('(hover: none)').matches;

	for (const code of root.querySelectorAll(selector)) {
		const pre = code.parentElement;
		if (pre.querySelector('[data-copy-code]')) {
			continue;
		}

		const button = document.createElement('button');
		button.type = 'button';
		button.dataset.copyCode = '';
		button.title = label;
		button.setAttribute('aria-label', label);
		button.className = 'btn btn-sm btn-secondary position-absolute top-0 end-0 m-2 lh-1 '
			+ (alwaysVisible ? 'opacity-75' : 'opacity-0');
		button.innerHTML = copyIcon;

		button.addEventListener('click', async () => {
			// the clipboard is missing or refused outside HTTPS and localhost; the icon then stays unchanged
			try {
				await navigator.clipboard.writeText(code.textContent);
			} catch {
				return;
			}

			button.innerHTML = doneIcon;
			setTimeout(() => button.innerHTML = copyIcon, 1500);
		});

		if (!alwaysVisible) {
			const show = (visible) => button.classList.replace(
				visible ? 'opacity-0' : 'opacity-75',
				visible ? 'opacity-75' : 'opacity-0',
			);
			pre.addEventListener('mouseenter', () => show(true));
			pre.addEventListener('mouseleave', () => show(false));
			button.addEventListener('focus', () => show(true));
			button.addEventListener('blur', () => show(false));
		}

		pre.classList.add('position-relative');
		pre.append(button);
	}
}
