<?php

namespace Nextras\Web;

use Nette\Application\Routers\Route;
use Nette\Application\Routers\RouteList;


class RouterFactory
{
	/**
	 * @return \Nette\Application\IRouter
	 */
	public function createRouter()
	{
		$router = new RouteList();

		$router[] = new Route('', 'Homepage:default');
		$router[] = new Route('<component>', 'Docs:Browser:default', Route::ONE_WAY);
		$router[] = new Route('<component>/docs/<version>/<chapter>', 'Docs:Browser:default');
		$router[] = new Route('<component>/docs/<version>/', 'Docs:Browser:default');
		$router[] = new Route('<component>/docs', 'Docs:Browser:default');

		return $router;
	}
}
