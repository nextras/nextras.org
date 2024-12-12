<?php

namespace Nextras\Web;

use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;


#[AsCommand('doc:update', 'Fetch new doc')]
class FetchDocCommand extends Command
{
	/** @var FetchDocService @inject */
	public $fetchDocService;


	protected function execute(InputInterface $input, OutputInterface $output): int
	{
		$this->fetchDocService->fetch($output);
		return 0;
	}
}
