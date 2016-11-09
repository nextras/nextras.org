<?php

namespace Nextras\Web;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;


class FetchDocCommand extends Command
{
	/** @var FetchDocService @inject */
	public $fetchDocService;


	protected function configure()
	{
		$this->setName('doc:update')->setDescription('Fetch new doc');
	}


	protected function execute(InputInterface $input, OutputInterface $output)
	{
		$this->fetchDocService->fetch($output);
	}
}
