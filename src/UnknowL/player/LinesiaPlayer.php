<?php

namespace UnknowL\player;

use pocketmine\nbt\tag\CompoundTag;
use pocketmine\player\Player;
use UnknowL\Linesia;
use UnknowL\player\PlayerProperties;
use UnknowL\player\manager\EconomyManager;
use UnknowL\rank\Rank;

final class LinesiaPlayer extends Player
{

	private PlayerProperties $properties;

	private EconomyManager $economyManager;

	private Rank $rank;

	public function initEntity(CompoundTag $nbt): void
	{
		parent::initEntity($nbt);
		$this->properties = new PlayerProperties($this);
		$this->economyManager = new EconomyManager($this);
		$this->rank = Linesia::getInstance()->getRankManager()->getRank($this->properties->getProperties("rank"));
	}


	final public function getEconomyManager(): EconomyManager
	{
		return $this->economyManager;
	}

	final public function getPlayerProperties(): PlayerProperties
	{
		return $this->properties;
	}

	/**
	 * @return Rank
	 */
	final public function getRank(): Rank
	{
		return $this->rank;
	}
}