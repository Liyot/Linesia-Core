<?php

namespace UnknowL;

use pocketmine\crafting\ShapedRecipe;
use pocketmine\inventory\CreativeInventory;
use pocketmine\item\Item;
use pocketmine\item\StringToItemParser;
use pocketmine\item\VanillaItems;
use pocketmine\plugin\PluginBase;
use pocketmine\Server;
use pocketmine\utils\SingletonTrait;
use UnknowL\handlers\Handler;
use UnknowL\items\armor\amethyst\AmethystHelmet;
use UnknowL\trait\LoaderTrait;

final class Linesia extends PluginBase
{
	use SingletonTrait, LoaderTrait;

	public function onEnable(): void
	{
		self::setInstance($this);
		$this->loadAll();
	}

	public function onDisable(): void
	{
		$this->saveAll();
	}
}