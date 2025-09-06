<?php

namespace Nextras\Web;

use Nette\Application\UI\Presenter;


abstract class BasePresenter extends Presenter
{
	public int $invalidLinkMode = self::InvalidLinkException;


	protected function beforeRender(): void
	{
		$this->template->appDir = __DIR__ . '/../';
		parent::beforeRender();
	}
}
