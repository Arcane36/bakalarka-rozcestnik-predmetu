<?php

namespace App;

use Nette;
use Nette\Application\Routers\RouteList;
use Nette\Application\Routers\Route;
use Nette\Application\Routers\SimpleRouter;

class RouterFactory
{

	/**
	 * @return Nette\Application\IRouter
	 */
	public static function createRouter()
	{
		$router = new RouteList;
		$router[] = new Route('<action>[/<id>]', 'Homepage:default',SimpleRouter::SECURED);
		$router[] = new Route('<presenter>/<action>[/<id>]', 'Homepage:default',SimpleRouter::SECURED);
		return $router;
	}

//	public static function createRouter()
//	{
//		$router = new RouteList;
//		$router[] = new Route('<presenter>/<action>[/<id [0-9]+>]', array(
//			'presenter' => 'Homepage',
//			'action' => 'default',
//		));
//		return $router;
//	}

}
