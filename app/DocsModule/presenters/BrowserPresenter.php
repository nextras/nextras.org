<?php

namespace Nextras\Web\Docs;

use Nette\Application\Responses\FileResponse;
use Nette\Http\IResponse;
use Nette\Utils\Strings;
use Nextras\Web\ComponentBasePresenter;
use Nextras\Web\NextrasComponent;


class BrowserPresenter extends ComponentBasePresenter
{
	/** @persistent @var string */
	public $version;

	/** @persistent @var string */
	public $chapter = 'default';

	/** @var bool */
	private $missing = FALSE;


	public function actionDefault($component)
	{
		$structure = $this['nextrasComponent']->getStructure();

		if (empty($this->version)) {
			$defaultVersion = $structure->getComponentDefaultVersion();
			if ($defaultVersion) {
				$this->redirect(IResponse::S302_FOUND, 'this', ['version' => $defaultVersion]);
			} else {
				$this->missing = TRUE;
			}
		} else {
			if (!$structure->pageExists()) {
				$this->error();
			}
		}
	}


	public function renderDefault()
	{
		if (!$this->missing) {
			$structure = $this['nextrasComponent']->getStructure();
			$file = $structure->getChapterFile();
			if (Strings::endsWith($file, '.texy')) {
				$this->template->activeComponent = $structure->getActiveComponentKey();
				$this->template->editLink = $structure->getEditLink();
				$this->template->chapter = $structure->getChapter();
			} else {
				$this->sendResponse(new FileResponse($file));
			}
		}
	}


	protected function createComponentNextrasComponent()
	{
		return new NextrasComponent($this->component, $this->version, $this->chapter);
	}
}
