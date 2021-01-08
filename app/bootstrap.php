<?php

namespace Nextras\Web;

use Nette\Configurator;


require __DIR__ . '/../vendor/autoload.php';
umask(0);


$configurator = new Configurator();
$configurator->enableDebugger(__DIR__ . '/../log');
$configurator->setTempDirectory(__DIR__ . '/../temp');
$configurator->addConfig(__DIR__ . '/config/config.neon');

$loader = $configurator->createRobotLoader();
$loader->addDirectory(__DIR__);
$loader->register();

return $configurator->createContainer();
