<?php

namespace Nextras\Web;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;


class FetchDocCommand extends Command
{
	static public $defaultName = 'doc:update';

	/** @var FetchDocService @inject */
	public $fetchDocService;


	protected function configure(): void
	{
		$this->setDescription('Fetch new doc');
	}


	protected function execute(InputInterface $input, OutputInterface $output): int
	{
		$this->fetchDocService->fetch($output);
		return 0;
	}
}
