<?php declare(strict_types = 1);

namespace Nextras\Web\Docs;

use MabeEnum\Enum;
use Nette\Utils\FileSystem;


interface Page
{
	function getVersion(): string;
}


class MediaFile implements Page
{
	public function __construct(
		private string $file,
		private string $version,
	)
	{
	}


	function getFileName(): string
	{
		return $this->file;
	}


	function getVersion(): string
	{
		return $this->version;
	}
}


class TextPage implements Page
{
	public function __construct(
		private string $file,
		private string $version,
		private string $editLink,
		private ContentType $type,
		public string $packageName,
		public string $packageRepoSlug,
	)
	{
	}


	function getVersion(): string
	{
		return $this->version;
	}


	function getEditLink(): string
	{
		return $this->editLink;
	}


	function getType(): ContentType
	{
		return $this->type;
	}


	function getContent(): string
	{
		return FileSystem::read($this->file);
	}


	function getCacheKey(): string
	{
		return md5($this->file);
	}
}


class ContentType extends Enum
{
	const TEXY = 'texy';
	const MARKDOWN = 'markdown';
}
