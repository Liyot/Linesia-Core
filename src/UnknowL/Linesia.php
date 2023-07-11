<?php

namespace UnknowL;

use pocketmine\plugin\PluginBase;
use pocketmine\utils\SingletonTrait;
use UnknowL\handlers\Handler;
use UnknowL\trait\LoaderTrait;

final class Linesia extends PluginBase
{
	use SingletonTrait, LoaderTrait;

	public function onEnable(): void
	{
		self::setInstance($this);
		$this->loadAll();
		Handler::SHOP()->getForm();
	}

	public function onDisable(): void
	{
		$this->saveAll();
	}
}