<?php

namespace UnknowL\player\manager;

use DateTimeInterface;
use JetBrains\PhpStorm\ArrayShape;
use pocketmine\nbt\tag\CompoundTag;
use pocketmine\nbt\tag\ListTag;
use pocketmine\Server;

final class StatManager extends PlayerManager
{

	public const TYPE_KILL = 0;

	public const TYPE_DEATH = 1;

	public const TYPE_BLOCK_PLACED = 2;

	public const TYPE_BLOCK_MINED = 3;

	public const TYPE_CONNEXION = 4;

	public const TYPE_DUAL = 5;

	private array $offlineStatsCache = [];

	#[ArrayShape([
		'kill' => 'int',
		'death' => 'int',
		'killstreak' => 'int',
		'kd' => 'float',
		'gametime' => 'int',
		'blockmined' => 'int',
		'blockposed' => 'int',
		'firstconnexion' => 'int',
		'lastconnexion' => 'int',
		'dualwon' => 'int'
	])]
	private array $statistics = [];

    protected function load(): void
    {
		$this->statistics = $this->deserialize();
	}

	private function deserialize(): array
	{
		$properties = $this->getPlayer()->getPlayerProperties();
		return $properties->getNestedProperties("manager.statistics") ?? $properties->getProperties("statistics") ?? [];
	}

    public function getAll(): array
    {
		return $this->statistics;
	}

    public function getName(): string
    {
		return "statistics";
	}

	public function handleEvents(int $types): void
	{
		match ($types)
		{
			self::TYPE_KILL => $this->addKill(),
			self::TYPE_DEATH => $this->addDeath(),
			self::TYPE_BLOCK_PLACED => $this->addPosedBlock(),
			self::TYPE_BLOCK_MINED => $this->addMinedBlock(),
			self::TYPE_CONNEXION => $this->setLastConnexion(),
			self::TYPE_DUAL => $this->updateDual()
		};
	}

	private function addKill(): void
	{
		$this->statistics["kill"] += 1;
		$this->recalculateRatio();
	}

	private function addDeath(): void
	{
		$this->statistics["death"] += 1;
		$this->recalculateRatio();
	}

	private function updateDual(): void
	{
		//TODO: when dual are done
	}

	private function addMinedBlock(): void
	{
		$this->statistics["blockmined"] += 1;
	}

	private function addPosedBlock(): void
	{
		$this->statistics["blockposed"] += 1;
	}

	final public function onFirstConnexion(): void
	{
		$this->statistics["firstconnexion"] = time();
	}

	final public function onConnexion(): void
	{
		$this->statistics["lastconnexion"] = time();
	}

	final public function recalculateGameTime(): void
	{
		$this->statistics["gametime"] += 60;
	}

    final public function formatGameTime(): string
    {
		return $this->convertUnixTime($this->statistics["gametime"]);
    }

	final protected function recalculateRatio(): void
	{
		$death = $this->statistics["death"];
		$death > 1 ?: $death = 1;
        $kd = round($this->statistics['kill'] / $death, 2);
        $this->statistics["kd"] = $kd;
	}

	private function setLastConnexion(): void
	{
		$this->statistics["lastconnexion"] = time();
	}

	final public function __toString(): string
	{
		$stats = $this->statistics;
		return sprintf
		(
			"§5Depuis le début de votre avanture vous avez : \n §d- Kill = %d joueur(s) \n §d- Death = %d fois \n - KD = %.2f. \n\n
		 §5En ce qui concerne le minage vous avez: \n §d- Placer %d blocks \n §d- Casser %d blocks \n\n §5Vous avez réalisé ces exploits avec un temps de jeu de %s",
			$stats["kill"], $stats["death"], $stats["kd"], $stats["blockposed"], $stats["blockmined"], $this->formatGameTime()
		);
	}

	private function convertUnixTime (int $unixTime): string
	{
		$seconds = $unixTime;

		$months = floor($seconds / (30 * 24 * 60 * 60));
		$seconds -= $months * 30 * 24 * 60 * 60;

		$days = floor($seconds / (24 * 60 * 60));
		$seconds -= $days * 24 * 60 * 60;

		$hours = floor($seconds / (60 * 60));
		$seconds -=  $hours * 60 * 60;

		$minutes = floor($seconds / 60);

		$convertedTime = '';

		if ($months > 0) {
			$convertedTime .= $months . ' mois ';
		}

		if ($days > 0) {
			$convertedTime .= $days . ' jour(s) ';
		}

		if ($hours > 0) {
			$convertedTime .= $hours . ' heure(s)';
		}

		if ($minutes > 0) {
			$convertedTime .= $minutes . " minute(s)";
		}

		return $convertedTime;
	}

	final public function getOfflinePlayerStatistics(string $name): self
	{
		$statistics = $this->TagtoArray(Server::getInstance()->getOfflinePlayerData($name)->getCompoundTag('properties')->getCompoundTag('statistics'));
		$statManager = clone $this;
		$statManager->statistics = $statistics;
		unset($statManager->player);
		unset($this->offlineStatsCache);
		return $statManager;
	}

	private function TagtoArray(CompoundTag|ListTag $nbt, $name = null): array{
		foreach($nbt->getValue() as $key => $value){
			if($value instanceof CompoundTag || $value instanceof ListTag){
				self::TagtoArray($value, array_search($value, $nbt->getValue(), true));
			}else{
				$name === null ? $this->offlineStatsCache[$key] = $value->getValue() : $this->offlineStatsCache[$name][$key] = $value->getValue();
			}
		}
		return $this->offlineStatsCache;
	}
}