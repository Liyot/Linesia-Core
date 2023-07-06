<?php

namespace UnknowL\handlers\specific;

use pocketmine\block\VanillaBlocks;
use pocketmine\utils\EnumTrait;

final class CasinoHandler
{
	use EnumTrait;
	public function __construct()
	{
		VanillaBlocks::AIR();
	}

	protected static function setup(): void
	{
		self::register();
	}
}