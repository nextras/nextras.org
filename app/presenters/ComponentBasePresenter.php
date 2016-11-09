<?php

namespace Nextras\Web;


abstract class ComponentBasePresenter extends BasePresenter
{
	/** @persistent @var string */
	public $component;


	protected function startup()
	{
		parent::startup();
		$this['nextrasComponent'];
	}


	protected function createComponentNextrasComponent()
	{
		return new NextrasComponent($this->component);
	}
}
