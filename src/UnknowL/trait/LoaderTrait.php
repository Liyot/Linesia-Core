<?php

namespace UnknowL\trait;

use pocketmine\Server;
use pocketmine\utils\Utils;
use UnknowL\commands\CommandManager;
use UnknowL\commands\kit\KitCommand;
use UnknowL\commands\money\MoneyCommand;
use UnknowL\kits\KitManager;
use UnknowL\lib\commando\exception\HookAlreadyRegistered;
use UnknowL\lib\commando\PacketHooker;
use UnknowL\Linesia;
use UnknowL\listener\PacketListener;
use UnknowL\listener\PlayerListener;
use UnknowL\rank\RankManager;

trait LoaderTrait
{

	private CommandManager $commandManager;

	private RankManager $rankManager;

	private KitManager $kitManager;

	/**
	 * @throws HookAlreadyRegistered
	 */
	final public function loadAll(): void
	{
		Linesia::getInstance()->getLogger()->notice("§b Chargement du core Faction");
		$this->loadLib();
		$this->loadManager();
		$this->loadResources();
		$this->loadCommands();
		$this->loadListeners();
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
		$this->rankManager = new RankManager();
		$this->kitManager = new KitManager();
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

	private function loadResources(): void
	{
		Linesia::getInstance()->saveResource("kits.json");
		Linesia::getInstance()->saveResource("rank.json");

	}

	public function loadCommands(): void
	{
		Server::getInstance()->getCommandMap()->register("", new KitCommand());
		Server::getInstance()->getCommandMap()->register("", new MoneyCommand());
	}
//Getters

	final public function getCommandManager(): CommandManager
	{
		return $this->commandManager;
	}

	final public function getRankManager(): RankManager
	{
		return $this->rankManager;
	}

	final public function getKitManager(): KitManager
	{
		return $this->kitManager;
	}

}