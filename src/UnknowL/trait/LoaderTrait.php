<?php

namespace UnknowL\trait;

use pocketmine\Server;
use UnknowL\commands\casino\CasinoCommand;
use UnknowL\commands\CommandManager;
use UnknowL\commands\kit\KitCommand;
use UnknowL\commands\market\MarketCommand;
use UnknowL\commands\money\MoneyCommand;
use UnknowL\commands\rank\RankCommand;
use UnknowL\commands\shop\ShopCommand;
use UnknowL\handlers\Handler;
use UnknowL\lib\commando\exception\HookAlreadyRegistered;
use UnknowL\lib\commando\PacketHooker;
use UnknowL\lib\libasynql\DataConnector;
use UnknowL\Linesia;
use UnknowL\listener\PacketListener;
use UnknowL\listener\PlayerListener;
use UnknowL\task\ClearlagTask;

trait LoaderTrait
{

	private CommandManager $commandManager;

	private ClearlagTask $ClearlagManager;

	private DataConnector $db;


	/**
	 * @throws HookAlreadyRegistered
	 */
	final public function loadAll(): void
	{
		Linesia::getInstance()->getLogger()->notice("§b Chargement du core Faction");
		$this->loadManager();
		$this->loadLib();
		$this->loadResources();
		$this->loadCommands();
		$this->loadListeners();
		$this->loadTask();
		$this->loadFolder();
		Linesia::getInstance()->getLogger()->notice("§a Activation du core Faction");
	}

	final public function saveAll(): void
	{
	}
	
	private function loadListeners(): void 
	{
		$pluginManager = Linesia::getInstance()->getServer()->getPluginManager();
		$pluginManager->registerEvents(new PlayerListener(), $this);
		$pluginManager->registerEvents(new PacketListener(), $this);
	}

	private function loadManager(): void
	{
		Handler::init();
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

	private function loadResources(): void
	{
		Linesia::getInstance()->saveResource("kits.json");
		Linesia::getInstance()->saveResource("rank.json");
		/**$this->db = libasynql::create($this, $this->getConfig()->get("database"), [
			"sqlite" => "sqlite.sql",
			"mysql" => "mysql.sql"
		]);**/

	}

	private function loadCommands(): void
	{
		Server::getInstance()->getCommandMap()->register("", new CasinoCommand());
		Server::getInstance()->getCommandMap()->register("", new KitCommand());
		Server::getInstance()->getCommandMap()->register("", new MoneyCommand());
		Server::getInstance()->getCommandMap()->register("", new ShopCommand());
		Server::getInstance()->getCommandMap()->register("", new MarketCommand());
		Server::getInstance()->getCommandMap()->register("", new RankCommand());
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

	/**
	 * @return ClearlagTask
	 */
	final public function getClearlagManager(): ClearlagTask
	{
		return $this->ClearlagManager;
	}

	final public function getDatabase(): DataConnector
	{
		return $this->db;
	}
}