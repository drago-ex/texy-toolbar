<?php declare(strict_types=1);

namespace Drago\Texy;

use function strlen;
use const ENT_QUOTES, ENT_SUBSTITUTE, PREG_OFFSET_CAPTURE, PREG_SET_ORDER, PREG_UNMATCHED_AS_NULL;


/**
 * Simple syntax highlighter. Each language is a list of regular expressions of tokens, the text between them
 * is only escaped. The tokens get Bootstrap 5 text classes, so the colors follow the light and dark theme.
 */
final class Highlighter
{
	/** Bootstrap 5 classes of the token types. */
	public const array Classes = [
		'comment' => 'text-body-secondary fst-italic',
		'string' => 'text-success',
		'number' => 'text-danger',
		'keyword' => 'text-primary',
		'variable' => 'text-warning-emphasis',
		'tag' => 'text-primary',
		'attribute' => 'text-info-emphasis',
		'macro' => 'text-danger-emphasis',
	];

	private const array Aliases = [
		'html' => 'latte',
		'javascript' => 'js',
	];

	private const string Strings = '\'(?:\\\.|[^\'\\\])*\'|"(?:\\\.|[^"\\\])*"';

	private const string Numbers = '\b\d[\d_]*(?:\.\d+)?\b';

	/** @var array<string, string> */
	private array $patterns = [];


	/** Returns HTML of the code, the code of an unknown language is only escaped. */
	public function highlight(string $code, string $language): string
	{
		$pattern = $this->getPattern($language);
		if ($pattern === null) {
			return $this->escape($code);
		}

		preg_match_all($pattern, $code, $matches, PREG_SET_ORDER | PREG_OFFSET_CAPTURE | PREG_UNMATCHED_AS_NULL);

		$html = '';
		$offset = 0;
		foreach ($matches as $match) {
			$token = (string) $match[0][0]; // the whole match is never null
			$start = $match[0][1];
			$html .= $this->escape(substr($code, $offset, $start - $offset));
			foreach (self::Classes as $type => $class) {
				if (isset($match[$type][0])) {
					$html .= '<span class="' . $class . '">' . $this->escape($token) . '</span>';
					break;
				}
			}

			$offset = $start + strlen($token);
		}

		return $html . $this->escape(substr($code, $offset));
	}


	public function isSupported(string $language): bool
	{
		return $this->getRules($language) !== null;
	}


	private function getPattern(string $language): ?string
	{
		$rules = $this->getRules($language);
		if ($rules === null) {
			return null;
		}

		return $this->patterns[$language] ??= '~' . implode('|', array_map(
			fn(string $type, string $regex): string => "(?<$type>$regex)",
			array_keys($rules),
			$rules,
		)) . '~';
	}


	/**
	 * Tokens of the language; at the same position the first rule wins.
	 * @return array<string, string>|null
	 */
	private function getRules(string $language): ?array
	{
		$language = strtolower($language);
		return match (self::Aliases[$language] ?? $language) {
			'php' => [
				'comment' => '//[^\n]*|\#(?!\[)[^\n]*|/\*[\s\S]*?\*/',
				'string' => self::Strings,
				'variable' => '\$\w+',
				'keyword' => '<\?php\b|\?>|(?i:\b(?:abstract|and|array|as|bool|break|case|catch|class|clone|const|continue'
					. '|declare|default|do|echo|else|elseif|empty|enum|exit|extends|false|final|finally|float|fn|for'
					. '|foreach|function|global|if|implements|include|instanceof|int|interface|isset|iterable|list'
					. '|match|mixed|namespace|never|new|null|object|or|parent|print|private|protected|public|readonly'
					. '|require|require_once|include_once|return|self|static|string|switch|throw|trait|true|try|unset'
					. '|use|void|while|yield)\b)',
				'number' => self::Numbers,
			],
			'neon' => [
				'comment' => '(?<!\S)\#[^\n]*',
				'string' => self::Strings,
				'attribute' => '[\w.-]+(?=:(?:\s|$))',
				'variable' => '%[\w.-]+%|@[\w\\\.]+',
				'keyword' => '(?i:\b(?:true|false|yes|no|on|off|null)\b)',
				'number' => self::Numbers,
			],
			'latte' => [
				'comment' => '<!--[\s\S]*?-->|\{\*[\s\S]*?\*\}',
				'macro' => '\{[/$=_a-zA-Z][^{}\n]*\}',
				'tag' => '</?[a-zA-Z][\w:-]*|/?>',
				'attribute' => '(?<=\s)[\w:.@-]+(?==)',
				'string' => '(?<==)"[^"]*"|(?<==)\'[^\']*\'',
			],
			'sql' => [
				'comment' => '--[^\n]*|\#[^\n]*|/\*[\s\S]*?\*/',
				'string' => self::Strings,
				'attribute' => '`[^`]*`',
				'keyword' => '(?i:\b(?:add|all|alter|and|as|asc|auto_increment|between|by|case|charset|collate|constraint'
					. '|count|create|cross|default|delete|desc|distinct|drop|else|end|engine|exists|foreign|from|group'
					. '|having|if|in|index|inner|insert|into|is|join|key|left|like|limit|not|null|offset|on|or|order'
					. '|outer|primary|references|right|select|set|table|then|union|unique|unsigned|update|values|when'
					. '|where|int|varchar|text|datetime|longtext)\b)',
				'number' => self::Numbers,
			],
			'css' => [
				'comment' => '/\*[\s\S]*?\*/',
				'string' => self::Strings,
				'keyword' => '@[\w-]+|!important',
				'attribute' => '(?<=[{;\s])[\w-]+(?=\s*:[^{;]*[;}])',
				'number' => '\#[0-9a-fA-F]{3,8}\b|\b\d+(?:\.\d+)?(?:px|r?em|%|v[hw]|m?s|deg|fr)?',
				'tag' => '[.\#][a-zA-Z_][\w-]*',
			],
			'js' => [
				'comment' => '//[^\n]*|/\*[\s\S]*?\*/',
				'string' => self::Strings . '|`(?:\\\.|[^`\\\])*`',
				'keyword' => '\b(?:async|await|break|case|catch|class|const|continue|default|delete|do|else|export'
					. '|extends|false|finally|for|function|if|import|in|instanceof|let|new|null|of|return|static|super'
					. '|switch|this|throw|true|try|typeof|undefined|var|void|while|yield)\b',
				'number' => self::Numbers,
			],
			default => null,
		};
	}


	private function escape(string $s): string
	{
		return htmlspecialchars($s, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
	}
}
