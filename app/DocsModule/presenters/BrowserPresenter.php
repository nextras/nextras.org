<?php declare(strict_types = 1);

namespace Nextras\Web\Docs;

use League\CommonMark\MarkdownConverter;
use League\CommonMark\Node\Inline\Text;
use Nette\Application\Attributes\Persistent;
use Nette\Application\Responses\FileResponse;
use Nette\DI\Attributes\Inject;
use Nette\DI\Container;
use Nette\NotImplementedException;
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
		$menu = $this->documentationService->get($this->component, $this->version, "menu");

		[$content, $title] = $this->parse($this->page);
		[$menuContent] = $menu !== null ? $this->parse($menu) : [null];

		$this->template->container = $this->container;
		$this->template->chapter = $this->page;
		$this->template->title = $title;
		$this->template->content = $content;
		$this->template->menuContent = $menuContent;
		$this->template->versions = $this->documentationService->getVersions($this->component);
	}


	/**
	 * @return array<string>
	 */
	private function parse(TextPage $page): array
	{
		if ($page->getType()->is(ContentType::TEXY)) {
			$texy = $this->container->getByType(\Texy::class);
			$output = $texy->process($page->getContent());
			return [$output, $texy->headingModule->title];
		} elseif ($page->getType()->is(ContentType::MARKDOWN)) {
			$converter = $this->container->getByType(MarkdownConverter::class);
			$parsed = $converter->convert($page->getContent());
			$heading = $parsed->getDocument()->firstChild()?->firstChild();
			if ($heading instanceof Text) {
				$title = $heading->getLiteral();
			} else {
				$title = "";
			}
			return [$parsed->getContent(), $title];
		} else {
			throw new NotImplementedException();
		}
	}
}
