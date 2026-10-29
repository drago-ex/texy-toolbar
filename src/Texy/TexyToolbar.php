<?php declare(strict_types=1);

namespace Drago\Texy;

use Nette\Http\IRequest;
use Nette\Localization\Translator;
use function is_string;


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

	/** Buttons of the toolbar for the basic Texy of TexyFormatter::toBasicHtml() (code is the language select). */
	public const array BasicButtons = ['bold', 'italic', 'link', 'list', 'numbered', 'quote', 'code'];


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
	 * Options for the basic Texy (e.g. comments): only the buttons of BasicButtons.
	 * @return array{preview: string, labels: array<string, string>, buttons: list<string>}
	 */
	public function getBasicOptions(string $previewUrl): array
	{
		return $this->getOptions($previewUrl) + ['buttons' => self::BasicButtons];
	}


	/**
	 * Answer of the preview: the HTML of the POST field texy, for sendJson().
	 * @return array{html: string}
	 */
	public function getPreview(): array
	{
		return ['html' => $this->formatter->toHtml($this->getText())];
	}


	/**
	 * Answer of the preview of the basic Texy.
	 * @return array{html: string}
	 */
	public function getBasicPreview(): array
	{
		return ['html' => $this->formatter->toBasicHtml($this->getText())];
	}


	private function getText(): ?string
	{
		$text = $this->httpRequest->getPost('texy');
		return is_string($text) ? $text : null;
	}
}
