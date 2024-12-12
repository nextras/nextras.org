<?php

namespace Nextras\Web\Docs;

use FSHL;
use Nette\Utils\Strings;
use Texy;


class TexyFactory
{
	public function create(): \Texy\Texy
	{
		$texy = new Texy\Texy();
		$texy->linkModule->root = '';
		$texy->alignClasses['left'] = 'left';
		$texy->alignClasses['right'] = 'right';
		$texy->headingModule->top = 2;
		$texy->headingModule->balancing = Texy\Modules\HeadingModule::FIXED;
		$texy->headingModule->generateID = true;
		$texy->tabWidth = 4;
		$texy->typographyModule->locale = 'en';
		$texy->allowed['longwords'] = false;
		$texy->allowed['block/html'] = false;
		$texy->imageModule->root = '';

		$texy->phraseModule->tags['phrase/strong'] = 'b';
		$texy->phraseModule->tags['phrase/em'] = 'i';
		$texy->phraseModule->tags['phrase/em-alt'] = 'i';
		$texy->phraseModule->tags['phrase/acronym'] = 'abbr';
		$texy->phraseModule->tags['phrase/acronym-alt'] = 'abbr';

		$texy->addHandler('block', [$this, 'blockHandler']);
		$texy->addHandler('heading', [$this, 'headingHandler']);
		return $texy;
	}


	/**
	 * @return Texy\HtmlElement
	 */
	public function blockHandler(
		Texy\HandlerInvocation $invocation,
		string $blocktype,
		string $content,
		string $lang = null,
		Texy\Modifier $modifier = null
	)
	{
		if (!in_array($blocktype, [
			'block/php',
			'block/neon',
			'block/javascript',
			'block/js',
			'block/css',
			'block/html',
			'block/htmlcb',
			'block/code',
			'block/default',
		], true)) {
			return $invocation->proceed($blocktype, $content, $lang, $modifier);
		}

		if (!$lang) {
			[, $lang] = explode('/', $blocktype);
		}
		$lang = strtolower($lang);
		if ($lang === 'js') {
			$lang = 'javascript';
		}

		$lexerClass = 'FSHL\\Lexer\\' . ucfirst($lang);
		$content = trim($content);
		if (class_exists($lexerClass)) {
			$fshl = new FSHL\Highlighter(new FSHL\Output\Html(), FSHL\Highlighter::OPTION_TAB_INDENT);
			$lexer = new $lexerClass();
			$content = Texy\Helpers::outdent($content);
			$content = $fshl->highlight($content, $lexer);
		}

		$texy = $invocation->getTexy();
		$content = $texy->protect($content, Texy\Texy::CONTENT_BLOCK);

		$elPre = Texy\HtmlElement::el('pre');
		if ($modifier) {
			$modifier->decorate($texy, $elPre);
		}

		$elPre->attrs['class'] = $lang ? 'src-' . $lang : null;
		$elPre->create('code', $content);
		return $elPre;
	}


	public function headingHandler(
		Texy\HandlerInvocation $invocation,
		$level,
		$content,
		Texy\Modifier $mod,
		$isSurrounded
	)
	{
		/** @var Texy\HtmlElement $result */
		$result = $invocation->proceed($level, $content, $mod, $isSurrounded);

		if (((int) substr($result->getName(), 1)) > 2) {
			$elAnchor = Texy\HtmlElement::el('a');
			$elAnchor->attrs['href'] = '#toc-' . Strings::webalize($content);
			$elAnchor->attrs['class'] = 'anchor';
			$elAnchor->setText('#');
			$result->add($elAnchor);
		}

		return $result;
	}
}
