<?php

namespace Nextras\Web;


class Link
{
	/** @var string */
	public $component;

	/** @var string */
	public $version;

	/** @var string */
	public $name;

	/** @var string */
	public $fragment;


	public function __construct(string $component, string $version = null, string $name = null, string $fragment = null)
	{
		$this->component = $component;
		$this->version = $version;
		$this->name = $name;
		$this->fragment = $fragment;
	}
}
