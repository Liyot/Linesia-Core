<?php

namespace UnknowL\games;

use UnknowL\games\types\BossGame;
use UnknowL\games\types\KothGame;
use UnknowL\games\types\NexusGame;
use UnknowL\games\types\OutpostGame;

enum GamesEnum : string
{

	const KOTH = KothGame::class;
	const NEXUS = NexusGame::class;
	const OUTPOST = OutpostGame::class;

	/**
	 * @return string
	 */
	public function getName(): string
	{
		return $this->name;
	}
}
