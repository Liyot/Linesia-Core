<?php

namespace UnknowL\kits;

use pocketmine\utils\Config;
use UnknowL\Linesia;
use UnknowL\rank\Rank;

final class KitManager
{
	/**
	 * @var Kit[]
	 */
	private array $kits = [];

	protected Config $config;

	public function __construct()
	{
		$this->config = new Config(Linesia::getInstance()->getDataFolder()."kits.json");
		$this->loadAll();
	}

	private function loadAll(): void
	{
		foreach ($this->config->get("kits") as $name => $data)
		{
			$this->loadKit($data["displayName"], $data["rank"],
				$data["contents"], $data["armorContents"], $data["contentDisplay"], $data["armorDisplay"], $data["cooldown"]);
		}
	}

	/**
	 * @param string $name
	 * @param string[] $ranks
	 * @param array $content
	 * @param array $armorContent
	 * @param array $contentDisplay
	 * @param array $armorDisplay
	 * @param array $cooldown
	 * @return void
	 */
	private function loadKit(string $name, array $ranks, array $content, array $armorContent, array $contentDisplay, array $armorDisplay, array $cooldown): void
	{
		$this->kits[strtolower($name)] = new Kit($name, $ranks, $content, $contentDisplay, $armorContent, $armorDisplay, $cooldown);
	}

	final public function create(string $name, array $ranks, array $content, array $armorContent, array $contentDisplay, array $armorDisplay): void
	{
		$this->config->set("kits", [$name => [$armorContent, $armorDisplay, $content, $contentDisplay, $ranks, $name]]);

	}

	final public function getKit(string $name): Kit
	{
		return $this->kits[strtolower($name)];
	}

	/**
	 * @return array
	 */
	public function getKits(): array
	{
		return $this->kits;
	}
}