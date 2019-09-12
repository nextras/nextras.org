<?php

namespace Nextras\Web;

use Nette\SmartObject;
use Nette\Utils\FileSystem;
use Symfony\Component\Console\Output\OutputInterface;


class FetchDocService
{
	use SmartObject;

	/** @var array */
	private $config;

	/** @var string */
	private $satelliteDir;

	/** @var string */
	private $docDir;


	public function __construct($config)
	{
		$this->config = $config;
		$this->satelliteDir = realpath(__DIR__ . '/../../satellites');
		$this->docDir = realpath(__DIR__ . '/../../docs');
	}


	public function fetch(OutputInterface $output)
	{
		FileSystem::delete($this->docDir);
		FileSystem::createDir($this->docDir);

		foreach ($this->config as $name => $minVersion) {
			$this->update($name, $output);
			$versions = $this->getVersions($name, $minVersion);
			foreach ($versions as $versionRef => $versionName) {
				$this->copy($name, $versionRef, $versionName, $output);
			}
		}
	}


	private function getVersions(string $name, string $minVersion)
	{
		$dir = $this->satelliteDir . '/' . $name;
		exec("cd $dir && git branch -r", $out);
		$out = array_map('trim', $out);
		$out = array_map(function (string $line) {
			return preg_replace('#origin/#', '', $line);
		}, $out);

		$out = array_filter($out, function (string $line) {
			return preg_match('#^(v\d+\.\d+|master)$#', $line) === 1;
		});
		$out = array_filter($out, function (string $line) use ($minVersion) {
			return $line === 'master' || substr($line, 1) >= substr($minVersion, 1);
		});
		$out = array_combine($out, $out);
		$out = array_map(function (string $line) {
			return $line === 'master' ? $line : substr($line, 1);
		}, $out);
		return $out;
	}


	private function update($name, OutputInterface $output)
	{
		$dir = $this->satelliteDir . '/' . $name;
		$output->writeln('Fetching ' . $name);

		if (!is_dir($dir)) {
			$url = 'https://github.com/nextras/' . $name . '.git';
			@mkdir($dir, 0777, TRUE);
			chdir($dir);
			exec('git clone ' . escapeshellarg($url) . ' .');
		} else {
			chdir($dir);
			exec('git fetch origin');
		}
	}


	private function copy($name, $versionRef, $versionName, OutputInterface $output)
	{
		$output->writeln('Copying ' . $name . ' - ' . $versionName);
		$dir = $this->satelliteDir . '/' . $name;
		chdir($dir);
		exec('git checkout origin/' . $versionRef);

		$source = $dir . '/doc';
		$target = $this->docDir . '/' . $name . '/' . $versionName;
		if (is_dir($target)) {
			FileSystem::delete($target);
		}

		@mkdir($target);
		if (!file_exists($source)) {
			$output->writeln('<error>Doc source ' . $source . ' does not exist.</error>');
			return;
		}
		FileSystem::copy($source, $target);
	}
}
