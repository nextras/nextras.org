<?php declare(strict_types = 1);

namespace Nextras\Web\Docs;

use Nette\SmartObject;
use Nette\Utils\Finder;
use Nette\Utils\Strings;


class DocumentationService
{
	use SmartObject;


	private string $root;


	public function __construct()
	{
		$this->root = realpath(__DIR__ . '/../../../docs');
	}


	/**
	 * @return array<string>
	 */
	function getVersions(string $component): array
	{
		$versions = [];
		$dir = "$this->root/$component";
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


	function get(string $component, ?string $version, ?string $chapter): ?Page
	{
		$chapter = $chapter ?? "default";

		if (Strings::match($component, /** @lang PhpRegExp */ '#^[a-zA-Z0-9_-]+$#') == null) {
			return null;
		}

		if ($version === null) {
			if (file_exists("$this->root/$component/main")) {
				$version = 'main';
			} elseif (file_exists("$this->root/$component/master")) {
				$version = 'master';
			} else {
				$version = '';
			}
		}

		if (Strings::match($version, /** @lang PhpRegExp */ '#^(\d+\.\d+|[a-z]+)$#') == null) {
			return null;
		}
		if (Strings::match($chapter, /** @lang PhpRegExp */ '#^[a-zA-Z0-9_.-]+$#') == null) {
			return null;
		}

		$files = [];

		// handle media files with already added extension
		$files[] = "docs/$chapter.md";
		$files[] = "doc/$chapter.texy";
		$files[] = "docs/$chapter";
		$files[] = "doc/$chapter";

		foreach ($files as $fileName) {
			$file = "$this->root/$component/$version/$fileName";
			if (!file_exists($file)) {
				continue;
			}

			$versionBranch = $version !== 'main' && $version !== 'master' ? "v$version" : $version;
			$editLink = "https://github.com/nextras/$component/blob/$versionBranch/$fileName?plain=1";
			$packageName = str_replace('-', ' ', Strings::capitalize($component));
			$packageRepoSlug = "nextras/$component";

			if (str_ends_with($file, '.md')) {
				return new TextPage(
					file: $file,
					version: $version,
					editLink: $editLink,
					type: ContentType::MARKDOWN(),
					packageName: $packageName,
					packageRepoSlug: $packageRepoSlug,
				);
			} elseif (str_ends_with($file, '.texy')) {
				return new TextPage(
					file: $file,
					version: $version,
					editLink: $editLink,
					type: ContentType::TEXY(),
					packageName: $packageName,
					packageRepoSlug: $packageRepoSlug,
				);
			} else {
				return new MediaFile(
					file: $file,
					version: $version,
				);
			}
		}

		return null;
	}
}
