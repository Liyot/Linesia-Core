<?php

namespace UnknowL\handlers;

use pocketmine\nbt\tag\CompoundTag;
use pocketmine\nbt\tag\ListTag;
use pocketmine\Server;

class OfflineDataHandler extends Handler
{
	private array $offlineStatsCache = [];

	private array $topStats = ["kill" => [1], 'death' => [1], 'killstreak' => [1], 'kd' => [0.1], 'gametime' => [1], 'blockmined' => [1], 'blockposed' => [1], "money" => [1]];

	public function __construct()
	{
		parent::__construct();
	}

	protected function loadData(): void
    {
		$folder = Server::getInstance()->getDataPath() . "/players";

		$this->loadTopStats();
	}

	public function loadTopStats(): void
	{
		$folder = Server::getInstance()->getDataPath() . "/players";

		foreach (scandir($folder) as $file)
		{
			if (in_array($file, ["..", ".", "..."])) continue;
			$playerName = substr($file, 0, stripos($file, ".dat"));
			$data = Server::getInstance()->getOfflinePlayerData($playerName);
			if (!is_null($data))
			{
				$data = $this->TagtoArray($data);
				$statistics =  [...$data["statistics"], "money" => $data["economy"]["money"] ?? $data["properties"]["money"] ?? $data["money"] ?? 0];
				unset($statistics["firstconnexion"], $statistics["lastconnexion"], $statistics["dualwon"]);
				array_walk($statistics, fn(mixed $value, string $key) => $value > array_values($this->topStats[$key])[0] ? $this->topStats[$key] = [$playerName => $value] : null);
			}
		}
		$this->topStats["gametime"][array_keys($this->topStats["gametime"])[0]] = $this->convertUnixTime($this->topStats["gametime"][array_keys($this->topStats["gametime"])[0]]);
	}
	protected function saveData(): void
    {}



	/**
	 * @return array
	 */
	final public function getTopStats(): array
	{
		return $this->topStats;
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
	public function getName(): string
	{
		return "OfflineData";
	}
}