<?php

namespace UnknowL;

use pocketmine\plugin\PluginBase;
use pocketmine\scheduler\ClosureTask;
use pocketmine\Server;
use pocketmine\utils\SingletonTrait;
use UnknowL\commands\CommandManager;
use UnknowL\trait\LoaderTrait;
use UnknowL\utils\Cooldown;

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