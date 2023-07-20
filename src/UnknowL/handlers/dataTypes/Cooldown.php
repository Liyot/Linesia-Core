<?php

namespace UnknowL\handlers\dataTypes;

use Cassandra\Date;
use pocketmine\Server;
use Ramsey\Uuid\Type\Time;
use UnknowL\Linesia;
use UnknowL\player\LinesiaPlayer;
use UnknowL\player\PlayerProperties;

abstract class Cooldown{

	private \DateTime $initialTime;

	public function __construct(private int $cooldownTime)
	{

	}

	public function isFinish() {
		$currentTime = time();

		if ($currentTime >= $this->cooldownTime) {
			return true;
		} else {
			return false;
		}
	}

	/**
	 * @return int
	 */
	public function getCooldownTime(): int
	{
		return $this->cooldownTime;
	}
}