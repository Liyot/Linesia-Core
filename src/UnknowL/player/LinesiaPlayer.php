<?php

namespace UnknowL\player;

use pocketmine\nbt\tag\CompoundTag;
use pocketmine\player\Player;
use UnknowL\player\manager\EconomyManager;

final class LinesiaPlayer extends Player
{

	private \PlayerProperties $properties;

	private EconomyManager $economyManager;

	public function initEntity(CompoundTag $nbt): void
	{
		parent::initEntity($nbt);
		$this->properties = new \PlayerProperties($this);
		$this->economyManager = new EconomyManager($this);
	}

	final public function getEconomyManager(): EconomyManager
	{
		return $this->economyManager;
	}

	final public function getPlayerProperties(): \PlayerProperties
	{
		return $this->properties;
	}
}