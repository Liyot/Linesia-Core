<?php

namespace UnknowL;

use pocketmine\plugin\PluginBase;
use pocketmine\utils\SingletonTrait;
use UnknowL\commands\CommandManager;
use UnknowL\trait\LoaderTrait;

final class Linesia extends PluginBase
{
	use SingletonTrait, LoaderTrait;

	public function onEnable(): void
	{
		self::setInstance($this);
		$this->loadAll();
	}
}