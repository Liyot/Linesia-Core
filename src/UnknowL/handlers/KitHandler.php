<?php

namespace UnknowL\handlers;

use pocketmine\utils\Config;
use UnknowL\handlers\dataTypes\Kit;
use UnknowL\Linesia;

final class KitHandler extends Handler
{
	/**
	 * @var Kit[]
	 */
	private array $kits = [];

	protected Config $config;

	public function __construct()
	{
		$this->config = new Config(Linesia::getInstance()->getDataFolder()."kits.json");
		parent::__construct();
	}

	private function loadAll(): void
	{
		foreach ($this->config->get("kits") as $name => $data)
		{
			$this->loadKit
			($data["displayName"],
				$data["permissions"],
				$data["contents"],
				$data["armorContents"],
				$data["contentDisplay"],
				$data["armorDisplay"],
				$data["cooldown"],
				$data["armorEnchant"],
				$data["contentEnchant"]
			);
		}
	}

	/**
	 * @param string $name
	 * @param string $ranks
	 * @param array $content
	 * @param array $armorContent
	 * @param array $contentDisplay
	 * @param array $armorDisplay
	 * @param string $cooldown
	 * @param array $armorEnchant
	 * @param array $contentEnchant
	 * @return void
	 */
	private function loadKit(string $name, string $ranks, array $content, array $armorContent, array $contentDisplay, array $armorDisplay, string $cooldown, array $armorEnchant, array $contentEnchant): void
	{
		$this->kits[strtolower($name)] = new Kit
		(
			$name,
			$ranks,
			$content,
			$contentDisplay,
			$armorContent,
			$armorDisplay,
			$cooldown,
			$armorEnchant,
			$contentEnchant
		);
	}

	final public function create(string $name, string $ranks, array $content, array $armorContent, array $contentDisplay, array $armorDisplay): void
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

	protected function loadData(): void { $this->loadAll(); }

	protected function saveData(): void {}

	public function getName(): string
	{
		return "kit";
	}
}