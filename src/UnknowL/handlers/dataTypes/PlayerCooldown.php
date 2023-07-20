<?php

namespace UnknowL\handlers\dataTypes;

use Cassandra\Date;
use pocketmine\Server;
use pocketmine\utils\Config;
use UnknowL\Linesia;
use UnknowL\player\LinesiaPlayer;
use UnknowL\utils\TimeUtils;

class PlayerCooldown extends Cooldown
{

	public function __construct(private int $cooldownTime, private LinesiaPlayer $player, private string $path, bool $save = false)
	{
		$player->addCooldown($this, $path);
		$save ?: $this->cooldownTime += time();
		parent::__construct($this->cooldownTime);
	}

	final public function end(): bool
	{
		if($this->cooldownTime < time())
		{
			$this->save($this->player);
			return true;
		}
		return false;
	}

	final public function format(): string
	{
		return TimeUtils::formatLeftTime($this->cooldownTime - time());
	}


	final public function save(LinesiaPlayer $player): void
	{
		$this->cooldownTime === 0 ?: $player->getPlayerProperties()->setNestedProperties($this->path, $this->cooldownTime);
	}

    final public function getPath(): string
    {
        return $this->path;
    }
}