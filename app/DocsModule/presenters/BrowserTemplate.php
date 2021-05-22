<?php declare(strict_types = 1);

namespace Nextras\Web\Docs;

use Nette\Application\UI\Control;
use Nette\Application\UI\Presenter;
use Nette\Bridges\ApplicationLatte\Template;
use Nette\DI\Container;


class BrowserTemplate extends Template
{
	public Container $container;

	public TextPage $chapter;

	/** @var array<string> */
	public array $versions;

	public Presenter $presenter;

	public Control $control;

	public string $baseUrl;

	public string $basePath;

	public string $title;

	public string $content;

	public ?string $menuContent;

	/** @var array<\stdClass> */
	public array $flashes = [];
}
