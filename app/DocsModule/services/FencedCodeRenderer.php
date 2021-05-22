<?php declare(strict_types = 1);

namespace Nextras\Web\Docs;

use FSHL;
use League\CommonMark\Extension\CommonMark\Node\Block\FencedCode;
use League\CommonMark\Node\Node;
use League\CommonMark\Renderer\ChildNodeRendererInterface;
use League\CommonMark\Renderer\NodeRendererInterface;
use League\CommonMark\Util\HtmlElement;
use League\CommonMark\Util\Xml;


class FencedCodeRenderer implements NodeRendererInterface
{
	public function __construct()
	{
	}


	public function render(Node $node, ChildNodeRendererInterface $childRenderer)
	{
		assert($node instanceof FencedCode);
		$lang = $this->getSpecifiedLanguage($node) ?? "";
		$lexerClass = 'FSHL\\Lexer\\' . ucfirst($lang);

		$content = $node->getLiteral();
		$content = trim($content);
		if (class_exists($lexerClass)) {
			$fshl = new FSHL\Highlighter(new FSHL\Output\Html(), FSHL\Highlighter::OPTION_TAB_INDENT);
			$lexer = new $lexerClass();
			$content = $fshl->highlight($content, $lexer);
		}

		return new HtmlElement('pre', [], $content);
	}


	protected function getSpecifiedLanguage(FencedCode $block): ?string
	{
		$infoWords = $block->getInfoWords();

		if (empty($infoWords) || empty($infoWords[0])) {
			return null;
		}

		return Xml::escape($infoWords[0]);
	}
}
