<?php

declare(strict_types=1);

namespace App\Router;

use Nette;
use Nette\Application\Routers\RouteList;
use Nette\Application\Routers\Route;

final class RouterFactory
{
	use Nette\StaticClass;

	public static function createRouter(): RouteList
	{
		$router = new RouteList;
		
		// Přidání hlavní stránky s formulářem pro rezervaci
		$router->addRoute('', 'Home:default');
		
		$router->addRoute('admin', 'Admin:default');
		// Přidání stránky s potvrzením rezervace
		$router->addRoute('reservation/<roomId>/<arrivalDate>/<departureDate>', 'Reservation:default');

		
		// Původní nastavení, které zachytává všechny ostatní URL formáty
		$router->addRoute('<presenter>/<action>[/<id>]', 'Home:default');

		return $router;
	}
}
