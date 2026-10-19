<?php

declare(strict_types=1);

namespace Drago\Texy;

use Texy\Configurator;
use Texy\HandlerInvocation;
use Texy\Helpers;
use Texy\HtmlElement;
use Texy\Modifier;
use Texy\Texy;


/**
 * Converts Texy markup to HTML. Raw HTML in the markup is limited to safe inline tags and URLs to safe schemes,
 * so the output can be printed without escaping even if the text is not written by a trusted person. Images
 * and links stay allowed. Code blocks (/--code php) are highlighted by the Highlighter.
 */
class TexyFormatter
{
	/**
	 * @param int $headingTop level of the first heading of the text; 2 when the page title is the <h1>
	 */
	public function __construct(
		private readonly Highlighter $highlighter = new Highlighter,
		private readonly int $headingTop = 2,
	) {
	}


	public function toHtml(?string $text): string
	{
		if ($text === null || trim($text) === '') {
			return '';
		}

		return $this->createTexy()->process($text);
	}


	/**
	 * Basic Texy for texts of visitors, e.g. comments: bold, italic, links with rel="nofollow", lists, quotes
	 * and code; no images, headings, tables or HTML tags.
	 */
	public function toBasicHtml(?string $text): string
	{
		if ($text === null || trim($text) === '') {
			return '';
		}

		return $this->createBasicTexy()->process($text);
	}


	/** Texy of toBasicHtml(). */
	public function createBasicTexy(): Texy
	{
		$texy = $this->createTexy();
		$texy->allowedTags = Texy::NONE;
		Configurator::disableImages($texy);
		$texy->allowed['heading/surrounded'] = false;
		$texy->allowed['heading/underlined'] = false;
		$texy->allowed['table'] = false;
		$texy->allowed['horizline'] = false;
		$texy->allowed['link/definition'] = false;
		$texy->allowed['html/comment'] = false;
		$texy->linkModule->forceNoFollow = true;

		// visitors expect a new line where they pressed Enter
		$texy->mergeLines = false;
		return $texy;
	}


	/** Texy with the safe settings; change it when a text needs less, e.g. comments without images. */
	public function createTexy(): Texy
	{
		$texy = new Texy;
		$texy->allowedTags = Configurator::$safeTags;
		$texy->allowedClasses = Texy::NONE;
		$texy->allowedStyles = Texy::NONE;
		$texy->urlSchemeFilters[Texy::FILTER_ANCHOR] = '#https?:|mailto:#A';
		$texy->urlSchemeFilters[Texy::FILTER_IMAGE] = '#https?:#A';
		$texy->addHandler('block', $this->highlightCode(...));
		$texy->headingModule->top = $this->headingTop;
		return $texy;
	}


	/** The highlighter escapes the code itself, other blocks and unknown languages are left to Texy. */
	private function highlightCode(
		HandlerInvocation $invocation,
		string $blocktype,
		string $content,
		?string $lang,
		Modifier $modifier,
	): HtmlElement|string|null
	{
		if ($blocktype !== 'block/code' || $lang === null || !$this->highlighter->isSupported($lang)) {
			return $invocation->proceed();
		}

		$code = Helpers::outdent($content);
		if ($code === '') {
			return "\n";
		}

		$texy = $invocation->getTexy();
		$pre = new HtmlElement('pre');
		$modifier->decorate($texy, $pre);
		$pre->attrs['class'] = 'bg-body-tertiary p-3 rounded';
		$pre->create('code', $texy->protect($this->highlighter->highlight($code, $lang), Texy::CONTENT_BLOCK));
		return $pre;
	}
}
