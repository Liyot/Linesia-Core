<?php

namespace UnknowL;

use pocketmine\item\VanillaItems;
use pocketmine\plugin\PluginBase;
use pocketmine\scheduler\ClosureTask;
use pocketmine\Server;
use pocketmine\utils\SingletonTrait;
use UnknowL\casino\CasinoHandler;
use UnknowL\casino\types\CasinoGame;
use UnknowL\casino\types\Escalier;
use UnknowL\commands\CommandManager;
use UnknowL\lib\inventoryapi\InventoryAPI;
use UnknowL\trait\InventoryContainerTrait;
use UnknowL\trait\LoaderTrait;
use UnknowL\handlers\dataTypes\Cooldown;

final class Linesia extends PluginBase
{
	use SingletonTrait, LoaderTrait;

	public function onEnable(): void
	{
		self::setInstance($this);
		$this->loadAll();
		CasinoHandler::ROULETTE();
	}

	public function onDisable(): void
	{
		$this->saveAll();
	}
}