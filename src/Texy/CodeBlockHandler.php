<?php

declare(strict_types=1);

namespace Drago\Texy;

use Texy\HandlerInvocation;
use Texy\Helpers;
use Texy\HtmlElement;
use Texy\Modifier;
use Texy\Texy;


/**
 * Texy block handler that highlights code blocks (/--code php) by the Highlighter. Other blocks and unknown
 * languages are left to Texy. Register it by $texy->addHandler('block', $handler).
 */
final class CodeBlockHandler
{
	public function __construct(
		private readonly Highlighter $highlighter,
		private readonly string $preClass = 'bg-body-tertiary p-3 rounded',
	) {
	}


	/** The highlighter escapes the code itself. */
	public function __invoke(
		HandlerInvocation $invocation,
		string $blocktype,
		string $content,
		?string $lang,
		Modifier $modifier,
	): HtmlElement|string|null
	{
		if ($blocktype !== 'block/code' || $lang === null || !$this->highlighter->isSupported($lang)) {
			/** @var HtmlElement|string|null $html other handlers and Texy itself */
			$html = $invocation->proceed();
			return $html;
		}

		$code = Helpers::outdent($content);
		if ($code === '') {
			return "\n";
		}

		$texy = $invocation->getTexy();
		$pre = new HtmlElement('pre');
		$modifier->decorate($texy, $pre);
		$pre->attrs['class'] = $this->preClass;
		$pre->create('code', $texy->protect($this->highlighter->highlight($code, $lang), Texy::CONTENT_BLOCK));
		return $pre;
	}
}
