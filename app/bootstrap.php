<?php

namespace Nextras\Web;


use Nette\Bootstrap\Configurator;

require __DIR__ . '/../vendor/autoload.php';
umask(0);


$configurator = new Configurator();
$configurator->enableTracy(__DIR__ . '/../log');
$configurator->setTempDirectory(__DIR__ . '/../temp');
$configurator->addConfig(__DIR__ . '/config/config.neon');

$loader = $configurator->createRobotLoader();
$loader->addDirectory(__DIR__);
$loader->register();

return $configurator->createContainer();
