<?php

namespace Nextras\Web;

use Nette\Application\BadRequestException;
use Nette\Application\UI\Control;


class NextrasComponent extends Control
{
	/** @var string */
	private $version;

	/** @var string */
	private $chapter;

	/** @var StructureService */
	private $structure;

	/** @var string */
	private $componentName;

	/** @var array */
	private $data;


	/**
	 * @param string $componentName
	 * @param string|NULL $version
	 * @param string|NULL $chapter
	 */
	public function __construct($componentName, $version = NULL , $chapter = NULL)
	{
		parent::__construct();

		$this->data = [
			'orm'           => ['Orm',          'nextras/orm'],
			'dbal'          => ['Dbal',         'nextras/dbal'],
			'datagrid'      => ['Datagrid',     'nextras/datagrid'],
			'forms'         => ['Forms',        'nextras/forms'],
			'migrations'    => ['Migrations',   'nextras/migrations'],
			'mail-panel'    => ['MailPanel',    'nextras/mail-panel'],
			'secured-links' => ['SecuredLinks', 'nextras/secured-links'],
			'static-router' => ['StaticRouter', 'nextras/static-router'],
			'link-factory'  => ['LinkFactory',  'nextras/link-factory'],
			'youtube-api'   => ['YoutubeApi',   'nextras/youtube-api'],
			'latte-macros'  => ['LatteMacros',  'nextras/latte-macros'],
		];

		if (!isset($this->data[$componentName])) {
			throw new BadRequestException;
		}

		$this->componentName = $componentName;
		$this->version = $version;
		$this->chapter = $chapter;
		$this->structure = new StructureService(
			realpath(__DIR__ . '/../../docs'),
			$this->componentName,
			$this->version,
			$this->chapter,
			$this->data[$componentName][1],
			$this->getVersionBranch()
		);
	}


	public function render()
	{
		$this->template->setFile(__DIR__ . '/NextrasComponent.latte');

		if ($this->version) {
			$this->template->lastStableLabel = $this->version;
			$this->template->lastStableRef = $this->getVersionBranch();
		} else {
			$this->template->lastStableLabel = 'master';
			$this->template->lastStableRef = 'master';
		}

		list($this->template->addonName, $this->template->addonGitHub) = $this->data[$this->componentName];
		$this->template->versions = $this->structure->getVersions();
		$this->template->currentVersion = $this->version;
		$this->template->render();
	}


	public function renderForkBadge()
	{
		list($this->template->addonName, $this->template->addonGitHub) = $this->data[$this->componentName];
		$this->template->setFile(__DIR__ . '/NextrasComponent-forkBadge.latte');
		$this->template->render();
	}


	public function renderMenu()
	{
		$this->template->componentMenu = $this->structure->getComponentMenu();
		$this->template->setFile(__DIR__ . '/NextrasComponent-menu.latte');
		$this->template->render();
	}


	public function getStructure()
	{
		return $this->structure;
	}


	public function getDocumentationMenu()
	{
		return $this->structure->getComponentMenu();
	}


	public function getDocumentationVersions()
	{
		return $this->structure->getVersions();
	}


	private function getVersionBranch(): string
	{
		return $this->version === 'master' ? 'master' : 'v' . $this->version;
	}
}
