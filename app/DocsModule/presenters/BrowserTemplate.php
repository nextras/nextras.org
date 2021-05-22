<?php declare(strict_types = 1);

namespace Nextras\Web\Docs;

use Latte\Attributes\TemplateFilter;
use Latte\Engine;
use Latte\Runtime\FilterInfo;
use League\CommonMark\MarkdownConverter;
use Nette\Application\UI\Control;
use Nette\Application\UI\Presenter;
use Nette\Bridges\ApplicationLatte\Template;
use Nette\DI\Container;
use Nette\NotImplementedException;


class BrowserTemplate extends Template
{
	public Container $container;

	public TextPage $chapter;

	public ?TextPage $menu;

	/** @var array<string> */
	public array $versions;

	public Presenter $presenter;

	public Control $control;

	public string $baseUrl;

	public string $basePath;

	/** @var array<\stdClass> */
	public array $flashes = [];


	#[TemplateFilter]
	function parse(
		FilterInfo $info,
		string $content,
		ContentType $contentType,
	): string
	{
		$info->contentType = Engine::CONTENT_HTML;

		if ($contentType->is(ContentType::TEXY)) {
			$texy = $this->container->getByType(\Texy::class);
			return $texy->process($content);
		} elseif ($contentType->is(ContentType::MARKDOWN)) {
			$converter = $this->container->getByType(MarkdownConverter::class);
			return $converter->convertToHtml($content)->getContent();
		} else {
			throw new NotImplementedException();
		}
	}
}
