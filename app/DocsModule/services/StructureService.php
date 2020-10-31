<?php

namespace Nextras\Web;

use Nette\Application\BadRequestException;
use Nette\SmartObject;
use Nette\Utils\Finder;
use Nette\Utils\Strings;


class StructureService
{
	use SmartObject;

	/** @var string */
	private $root;

	/** @var TexyConverterService */
	private $converter;

	/** @var string */
	private $chapter;

	/** @var string */
	private $version;

	/** @var string */
	private $component;

	/** @var string */
	private $componentRepo;

	/** @var string */
	private $versionBranch;


	public function __construct($root, $component, $version, $chapter, string $componentRepo, string $versionBranch)
	{
		$this->root = $root;
		$this->component = $component;
		$this->version = $version;
		$this->chapter = $chapter;
		$this->converter = new TexyConverterService($component, $this->version, $this->chapter);
		$this->componentRepo = $componentRepo;
		$this->versionBranch = $versionBranch;
	}


	public function pageExists()
	{
		return file_exists($this->getChapterFile());
	}


	public function getVersions()
	{
		$versions = [];

		$dir = $this->root . '/' . $this->component;
		if (!is_dir($dir)) {
			return $versions;
		}

		$dirs = Finder::findDirectories('*')->in($dir);
		foreach ($dirs as $dir) {
			$versions[] = $dir->getFilename();
		}
		rsort($versions, SORT_NATURAL);
		$versions[] = array_shift($versions);
		return $versions;
	}


	public function getComponentDefaultVersion()
	{
		$versions = $this->getVersions();
		return reset($versions);
	}


	public function getComponentMenu()
	{
		$version = $this->version ?: $this->getComponentDefaultVersion();
		$menuFile = "{$this->root}/{$this->component}/{$version}/menu.texy";

		if (!file_exists($menuFile)) {
			return FALSE;
		}

		list($html) = $this->converter->parse(file_get_contents($menuFile));
		return $html;
	}


	public function getActiveComponentKey()
	{
		return $this->component;
	}


	public function getChapter()
	{
		$content = file_get_contents($this->getChapterFile());
		list($html) = $this->converter->parse($content);
		return $html;
	}


	public function getEditLink(): string
	{
		// https://github.com/nextras/orm/blob/master/doc/events.texy
		$chapter = $this->chapter ?: 'default';
		return "https://github.com/{$this->componentRepo}/blob/{$this->versionBranch}/doc/{$chapter}.texy";
	}


	public function getChapterFile()
	{
		if (empty($this->component) && empty($this->version) && empty($this->chapter)) {
			return $this->root . '/default.texy';
		}

		$chapter = $this->chapter ?: 'default';

		$file = $this->component . '/' . $this->version . '/' . $chapter;
		if (!Strings::match($file, '#^[\w-]+/(master|\d+\.\d+|\d+\.x)/[\w._-]+$#')) {
			throw new BadRequestException($file);
		}

		return $this->root . '/' . $file . (Strings::endsWith($file, '.png') || Strings::endsWith($file, '.jpeg') ? '' : '.texy');
	}
}
