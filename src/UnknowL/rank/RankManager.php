<?php

namespace UnknowL\rank;

use pocketmine\utils\Config;
use pocketmine\utils\SingletonTrait;
use UnknowL\Linesia;
use UnknowL\player\LinesiaPlayer;

final class RankManager
{

	protected Config $config;

	/**
	 * @var Rank[]
	 */
	private array $ranks = [];

	public function __construct()
	{
		$this->config = new Config(Linesia::getInstance()->getDataFolder()."rank.json");
		$this->loadAll();
	}

	final public function getRank(string $name): Rank
	{
		return $this->ranks[strtolower($name)];
	}

	final public function getDefaultRank(): Rank
	{
		return array_values(array_filter($this->ranks, fn(Rank $rank) =>  $rank->isDefault()))[0];
	}

	private function loadAll(): void
	{
		foreach ($this->config->get("ranks") as $name => $data)
		{
			$this->loadRank($data["displayName"], $data["permissions"], $data["chatFormat"], $data["default"]);
		}
	}

	private function loadRank(string $name, array $permissions, string $chatFormat, bool $isDefault): void
	{
		$this->ranks[strtolower($name)] = new Rank($name, $chatFormat, $permissions, $isDefault);
	}

}