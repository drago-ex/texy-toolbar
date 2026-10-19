<?php

declare(strict_types=1);

namespace Drago\Texy;

use Nette\Http\IRequest;
use Nette\Localization\Translator;


/**
 * Server side of the toolbar: the options of the data-texy-toolbar attribute and the HTML of the preview.
 */
class TexyToolbar
{
	/** Labels of the toolbar, translated by the translator of the application. */
	public const array Labels = [
		'bold' => 'Bold',
		'italic' => 'Italic',
		'heading' => 'Heading',
		'list' => 'List',
		'numbered' => 'Numbered list',
		'quote' => 'Quote',
		'link' => 'Link',
		'image' => 'Image',
		'code' => 'Code',
		'preview' => 'Preview',
		'edit' => 'Edit',
		'previewError' => 'The preview could not be loaded.',
	];


	public function __construct(
		private readonly TexyFormatter $formatter,
		private readonly IRequest $httpRequest,
		private readonly ?Translator $translator = null,
	) {
	}


	/**
	 * Options of the data-texy-toolbar attribute: the URL of the preview (e.g. a signal) and translated labels.
	 * @return array{preview: string, labels: array<string, string>}
	 */
	public function getOptions(string $previewUrl): array
	{
		return [
			'preview' => $previewUrl,
			'labels' => array_map(
				fn(string $label): string => $this->translator ? (string) $this->translator->translate($label) : $label,
				self::Labels,
			),
		];
	}


	/**
	 * Answer of the preview: the HTML of the POST field texy, for sendJson().
	 * @return array{html: string}
	 */
	public function getPreview(): array
	{
		$text = $this->httpRequest->getPost('texy');
		return ['html' => $this->formatter->toHtml(is_string($text) ? $text : null)];
	}
}
