<?php

namespace UnknowL\utils;

use pocketmine\Server;
use UnknowL\player\LinesiaPlayer;

abstract class CommandUtils
{

	const PERMISSION_ERROR_MESSAGE = "§cVous n'avez pas les permissions pour éxécuter cette commande !";

	final public static function checkTarget(string $name): ?LinesiaPlayer
	{
		$player = Server::getInstance()->getPlayerExact($name);
		return ($player instanceof LinesiaPlayer) ? $player : null;
	}
}