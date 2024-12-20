<?php

namespace Nextras\Web;

use Nette\Application\UI\Presenter;


abstract class BasePresenter extends Presenter
{
	public $invalidLinkMode = self::INVALID_LINK_EXCEPTION;


	protected function beforeRender(): void
	{
		$this->template->appDir = __DIR__ . '/../';
		parent::beforeRender();
	}
}
