<?php

namespace Nextras\Web;

use Nette\Application\Routers\Route;
use Nette\Application\Routers\RouteList;
use Nette\Routing\Router;


class RouterFactory
{
	public function createRouter(): Router
	{
		$router = new RouteList();

		$router->addRoute('', 'Homepage:default');
		$router->addRoute('<component>', 'Docs:Browser:default', Route::ONE_WAY);
		$router->addRoute('<component>/docs/<version>/<chapter>', 'Docs:Browser:default');
		$router->addRoute('<component>/docs/<version>/', 'Docs:Browser:default');
		$router->addRoute('<component>/docs', 'Docs:Browser:default');

		return $router;
	}
}
