<?php

namespace UnknowL\utils;

use pocketmine\Server;
use pocketmine\world\Position;

abstract class PositionUtils
{
	private static array $dualRoom = [0 => [150, 60, 25, "world"], 1 => [150, 60, 25, "world"]];

	const DUAL_1V1 = 0;
	const DUAL_2V2 = 1;

	//TODO: A refaire qd on aura les positions des duels
	public static function getAvailableDualRoom(int $type): Position
	{
		$room = self::$dualRoom[$type];
		return new Position($room[0], $room[1], $room[2], $room[3]);
	}

	public static function getSpawnPosition(): Position
	{
		return new Position(50, 42, 390, Server::getInstance()->getWorldManager()->getWorldByName('world'));
	}
}