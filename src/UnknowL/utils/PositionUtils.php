<?php

namespace UnknowL\utils;

use pocketmine\Server;
use pocketmine\world\Position;

abstract class PositionUtils
{
	private static array $dualRoom = [150, 60, 25, "world"];

	//TODO: A refaire qd on aura les positions des duels
	public static function getAvailableDualRoom(): Position
	{
		return new Position(self::$dualRoom[0], self::$dualRoom[1], self::$dualRoom[2], self::$dualRoom[3]);
	}

	public static function getSpawnPosition(): Position
	{
		return new Position(50, 42, 390, Server::getInstance()->getWorldManager()->getWorldByName('world'));
	}
}