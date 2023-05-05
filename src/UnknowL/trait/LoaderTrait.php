<?php

namespace UnknowL\trait;

use pocketmine\utils\Utils;
use UnknowL\lib\inventoryapi\events\PacketListener;
use UnknowL\commands\CommandManager;
use UnknowL\lib\commando\exception\HookAlreadyRegistered;
use UnknowL\lib\commando\PacketHooker;
use UnknowL\Linesia;
use UnknowL\listener\PlayerListener;

trait LoaderTrait
{

	private CommandManager $commandManager;

	/**
	 * @throws HookAlreadyRegistered
	 */
	final public function loadAll(): void
	{
		Linesia::getInstance()->getLogger()->notice("§b Chargement du core Faction");
		$this->loadLib();
		$this->loadManager();
		Linesia::getInstance()->getLogger()->notice("§a Activation du core Faction");
	}
	
	private function loadListeners(): void 
	{
		$pluginManager = Linesia::getInstance()->getServer()->getPluginManager();
		$pluginManager->registerEvents(new PlayerListener(), $this);
		$pluginManager->registerEvents(new PacketListener(), $this);
	}

	private function loadManager(): void
	{
		$this->commandManager = new CommandManager();
	}

	/**
	 * @throws HookAlreadyRegistered
	 */
	private function loadLib(): void
	{
		if (!PacketHooker::isRegistered()) {
			PacketHooker::register(Linesia::getInstance());
		}
	}

//Getters

	final public function getCommandManager(): CommandManager
	{
		return $this->commandManager;
	}

}