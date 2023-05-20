<?php

namespace UnknowL\trait;

use pocketmine\Server;
use pocketmine\utils\Filesystem;
use UnknowL\commands\CommandManager;
use UnknowL\commands\kit\KitCommand;
use UnknowL\commands\money\MoneyCommand;
use UnknowL\commands\shop\ShopHandler;
use UnknowL\commands\shop\ShopCommand;
use UnknowL\kits\KitManager;
use UnknowL\lib\commando\exception\HookAlreadyRegistered;
use UnknowL\lib\commando\PacketHooker;
use UnknowL\Linesia;
use UnknowL\listener\PacketListener;
use UnknowL\listener\PlayerListener;
use UnknowL\rank\RankManager;
use UnknowL\task\ClearlagTask;

trait LoaderTrait
{

	private CommandManager $commandManager;

	private RankManager $rankManager;

	private KitManager $kitManager;

	private ClearlagTask $ClearlagManager;

	private ShopHandler $shopHandler;

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
		$this->loadTask();
		$this->loadFolder();
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
		$this->shopHandler = new ShopHandler();
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

	private function loadCommands(): void
	{
		Server::getInstance()->getCommandMap()->register("", new KitCommand());
		Server::getInstance()->getCommandMap()->register("", new MoneyCommand());
		Server::getInstance()->getCommandMap()->register("", new ShopCommand());
	}

	private function loadTask(): void
	{
		Linesia::getInstance()->getScheduler()->scheduleRepeatingTask(new ClearlagTask(), 20 * 60 * 5);
	}

	private function loadFolder(): void
	{
		@mkdir(Linesia::getInstance()->getDataFolder().DIRECTORY_SEPARATOR."data");
		@mkdir(Linesia::getInstance()->getDataFolder().DIRECTORY_SEPARATOR."data".DIRECTORY_SEPARATOR."shop");
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

	/**
	 * @return ClearlagTask
	 */
	public function getClearlagManager(): ClearlagTask
	{
		return $this->ClearlagManager;
	}

	/**
	 * @return ShopHandler
	 */
	final public function getShopHandler(): ShopHandler
	{
		return $this->shopHandler;
	}

}