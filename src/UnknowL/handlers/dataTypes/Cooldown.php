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

	public function __construct(private \DateTime $cooldownTime)
	{
	}

	public function isFinish() {
		$currentTime = time();
		$elapsedTime = $currentTime - $this->cooldownTime->getTimestamp();

		if ($elapsedTime >= $this->cooldownTime) {
			return true;
		} else {
			return false;
		}
	}
}