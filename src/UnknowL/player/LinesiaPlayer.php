<?php

namespace UnknowL\player;

use pocketmine\lang\Translatable;
use pocketmine\nbt\tag\CompoundTag;
use pocketmine\nbt\tag\ListTag;
use pocketmine\player\Player;
use UnknowL\handlers\dataTypes\PlayerCooldown;
use UnknowL\Linesia;
use UnknowL\player\PlayerProperties;
use UnknowL\player\manager\EconomyManager;
use UnknowL\rank\Rank;
use UnknowL\handlers\dataTypes\Cooldown;

final class LinesiaPlayer extends Player
{

	private PlayerProperties $properties;

	private EconomyManager $economyManager;

	/***
	 * @var Cooldown[]
	 */
	private array $cooldowns = [];

	private Rank $rank;

	public function initEntity(CompoundTag $nbt): void
	{
		parent::initEntity($nbt);
		$this->properties = new PlayerProperties($this);
		$this->economyManager = new EconomyManager($this);
		$this->rank = Linesia::getInstance()->getRankManager()->getRank($this->properties->getProperties("rank"));
		$this->properties->setProperties("permissions" ,$this->getRank()->getPermissions());
		$this->loadCooldowns($nbt);
	}

	public function saveNBT(): CompoundTag
	{
		$nbt = parent::saveNBT();
		!isset($this->properties) ?: $this->properties->save($nbt);

		return $nbt;
	}

	final public function hasPermission($name): bool
	{
		return in_array($name, $this->getRank()->getPermissions(), true) || parent::hasPermission($name);
	}

	final public function addPermission(string $perm)
	{
		$permissions = $this->getPlayerProperties()->getProperties("permissions");
		$permissions[] = $perm;
		$this->getPlayerProperties()->setProperties("permissions", $perm);
	}

	final public function addCooldown(PlayerCooldown $cooldown, string $path, ...$args): void
	{
		$this->cooldowns[sprintf($path, $args)] = $cooldown;
	}

	final public function getCooldown(string $path, $args): ?Cooldown
	{
		return $this->cooldowns[sprintf($path, $args)] ?? null;
	}

	final public function loadCooldowns(CompoundTag|ListTag $array, $path = ""): void
	{
		foreach ($array->getValue() as $key => $properties)
		{
			if($key === "cooldown" && $properties->getValue() !== "'null'")
			{
				$data = explode(";", $properties->getValue());
				var_dump($data, $properties, $key);
				$cooldown = new PlayerCooldown(\DateTime::createFromFormat("d:H:i:s", $data[0]), $this, $data[1], true,
					\DateTime::createFromFormat("d:H:i:s",$data[2]));
				$this->addCooldown($cooldown,empty($path) ? $key : $path);
				return;
			}

			if($properties instanceof CompoundTag || $properties instanceof ListTag)
			{
				$this->loadCooldowns($properties, $path.$key);
			}
		}
	}

	final public function onPostDisconnect(string $reason, Translatable|string|null $quitMessage): void
	{
		foreach ($this->cooldowns as $cooldown)
		{
			$cooldown->save($this);
		}
		var_dump($this->properties);
		parent::onPostDisconnect($reason, $quitMessage);
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

	final public function setRank(Rank $rank): self
	{
		$this->rank = $rank;
		return $this;
	}
}