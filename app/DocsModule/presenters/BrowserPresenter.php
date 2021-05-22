<?php declare(strict_types = 1);

namespace Nextras\Web\Docs;

use Nette\Application\Attributes\Persistent;
use Nette\Application\Responses\FileResponse;
use Nette\DI\Attributes\Inject;
use Nette\DI\Container;
use Nextras\Web\BasePresenter;


/**
 * @property BrowserTemplate $template
 */
class BrowserPresenter extends BasePresenter
{
	#[Persistent]
	public string $component;

	#[Persistent]
	public ?string $version = null;

	#[Persistent]
	public string $chapter = "default";

	#[Inject]
	public DocumentationService $documentationService;

	#[Inject]
	public Container $container;

	private TextPage $page;


	public function actionDefault(string $component, string $chapter = "default")
	{
		$page = $this->documentationService->get($this->component, $this->version, $this->chapter);
		if ($page === null) {
			$this->error();
		}

		$this->version = $page->getVersion();

		if ($page instanceof MediaFile) {
			$this->sendResponse(new FileResponse($page->getFileName()));
		}

		assert($page instanceof TextPage);
		$this->page = $page;
	}


	public function renderDefault()
	{
		$this->template->container = $this->container;
		$this->template->chapter = $this->page;
		$this->template->menu = $this->documentationService->get($this->component, $this->version, "menu");
		$this->template->versions = $this->documentationService->getVersions($this->component);
	}
}
