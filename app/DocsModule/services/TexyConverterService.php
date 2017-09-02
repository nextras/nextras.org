<?php

namespace Nextras\Web;

use Nette\Utils\Strings;
use Texy;
use FSHL;


class TexyConverterService
{
	const HOMEPAGE = 'default';

	/** @var Link */
	private $current;


	public function __construct(string $component, string $version = null, string $name = null)
	{
		$this->current = new Link($component, $version, $name);
	}


	public function parse($text)
	{
		$texy = $this->createTexy();
		$html = $texy->process($text);
		$title = $texy->headingModule->title;
		return [$html, $title];
	}


	private function createTexy()
	{
		$texy = new Texy\Texy();
		$texy->linkModule->root = '';
		$texy->alignClasses['left'] = 'left';
		$texy->alignClasses['right'] = 'right';
		$texy->emoticonModule->class = 'smiley';
		$texy->headingModule->top = 2;
		$texy->headingModule->generateID = TRUE;
		$texy->tabWidth = 4;
		$texy->typographyModule->locale = 'en';
		$texy->tableModule->evenClass = 'alt';
		$texy->dtd['body'][1]['style'] = TRUE;
		$texy->allowed['longwords'] = FALSE;
		$texy->allowed['block/html'] = FALSE;
		$texy->imageModule->root = '';

		$texy->phraseModule->tags['phrase/strong'] = 'b';
		$texy->phraseModule->tags['phrase/em'] = 'i';
		$texy->phraseModule->tags['phrase/em-alt'] = 'i';
		$texy->phraseModule->tags['phrase/acronym'] = 'abbr';
		$texy->phraseModule->tags['phrase/acronym-alt'] = 'abbr';

		$texy->addHandler('block', [$this, 'blockHandler']);
		$texy->addHandler('phrase', [$this, 'phraseHandler']);
		$texy->addHandler('newReference', [$this, 'newReferenceHandler']);
		$texy->addHandler('heading', [$this, 'headingHandler']);
		return $texy;
	}


	/**
	 * @return Link|string
	 */
	public function resolveLink(string $link)
	{
		if (preg_match('~.+@|https?:|ftp:|mailto:|ftp\.|www\.~Ai', $link)) { // external link
			return $link;

		} elseif (substr($link, 0, 1) === '#') { // section link
			if (substr($link, 0, 5) === '#toc-') {
				$link = substr($link, 5);
			}
			return '#toc-' . Strings::webalize($link);
		}

		preg_match('~^
			(?:(?P<component>[a-z]+:))?
			(?:[:/]?(?P<version>[a-z]{2})(?=[:/#]|$))?
			(?P<name>[^#]+)?
			(?:\#(?P<fragment>.*))?
		$~x', $link, $matches);

		if (!$matches) {
			return $link; // invalid link
		}

		$matches = (object) $matches;
		$component = $matches->component ?: $this->current->component;
		$name = $matches->name ?: '';
		$name = trim(strtr($name, ':', '/'), '/');
		$version = $matches->version ?: $this->current->version;
		$fragment = $matches->fragment ?? $this->current->fragment;
		$fragment = substr($fragment, 0, 4) === 'toc-' ? substr($fragment, 4) : $fragment;
		return new Link($component, $version, $name, $fragment ? 'toc-' . Strings::webalize($fragment) : null);
	}


	public function createUrl(Link $link): string
	{
		$name = Strings::webalize($link->name, '._/');
		$version = $link->version ?: $this->current->version;
		return '/' . $link->component . '/docs/' . $version
			. ($name === self::HOMEPAGE ? '' : '/' . $name)
			. ($link->fragment ? "#$link->fragment" : '');
	}


	/**
	 * @return Texy\HtmlElement|string|FALSE
	 */
	public function phraseHandler(Texy\HandlerInvocation $invocation, string $phrase, string $content, Texy\Modifier $modifier, Texy\Link $link = null)
	{
		if (!$link) {
			return $invocation->proceed();
		}

		$dest = $this->resolveLink($link->URL);
		if ($dest instanceof Link) {
			$link->URL = $this->createUrl($dest);
			$dest->name = Strings::webalize($dest->name, '/');
			$dest->fragment = NULL;
			$this->links[] = $dest;
		} else {
			$link->URL = $dest;
		}

		return $invocation->proceed($phrase, $content, $modifier, $link);
	}


	/**
	 * @return Texy\HtmlElement|string|FALSE
	 */
	public function newReferenceHandler(Texy\HandlerInvocation $invocation, string $name)
	{
		$parts = explode('|', $name);
		if (isset($parts[1])) {
			$dest = trim($parts[1]);
			$label = trim($parts[0]);
		} else {
			$dest = $name;
		}

		$texy = $invocation->getTexy();

		$dest = $this->resolveLink($dest);
		if ($dest instanceof Link) {
			if (!isset($label)) {
				$label = explode('/', $dest->name);
				$label = end($label);
			}
			$el = $texy->linkModule->solve(NULL, new \Texy\Link($this->createUrl($dest)), $label);
			if ($dest->version !== $this->current->version) {
				$el->version = $dest->version;
			}

			$dest->name = Strings::webalize($dest->name, '/');
			$dest->fragment = NULL;

		} else {
			if (!isset($label)) {
				$label = preg_replace('#(?!http|ftp|mailto)[a-z]+:|\##A', '', $name); // [api:...], [#section]
			}
			$el = $texy->linkModule->solve(NULL, $texy->linkModule->factoryLink("[$dest]", NULL, $label), $label);
		}
		return $el;
	}


	/**
	 * @return Texy\HtmlElement
	 */
	public function blockHandler(Texy\HandlerInvocation $invocation, string $blocktype, string $content, string $lang = null, Texy\Modifier $modifier = null)
	{
		if (!in_array($blocktype, ['block/php', 'block/neon', 'block/javascript', 'block/js', 'block/css', 'block/html', 'block/htmlcb', 'block/code', 'block/default'], true)) {
			return $invocation->proceed($blocktype, $content, $lang, $modifier);
		}

		if (!$lang) {
			list(, $lang) = explode('/', $blocktype);
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

		$elPre->attrs['class'] = $lang ? 'src-' . $lang : NULL;
		$elPre->create('code', $content);
		return $elPre;
	}


	public function headingHandler(Texy\HandlerInvocation $invocation, $level, $content, Texy\Modifier $mod, $isSurrounded) {
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
