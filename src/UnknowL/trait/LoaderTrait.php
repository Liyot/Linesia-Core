<?php

namespace UnknowL\trait;

use pocketmine\command\defaults\SayCommand;
use pocketmine\entity\EntityDataHelper;
use pocketmine\entity\EntityFactory;
use pocketmine\nbt\tag\CompoundTag;
use pocketmine\Server;
use pocketmine\world\World;
use UnknowL\api\MineAPI;
use UnknowL\commands\admin\ClearLaggCommand;
use UnknowL\commands\admin\GiveAllCommand;
use UnknowL\commands\admin\IdCommand;
use UnknowL\commands\admin\MinageNPCCommand;
use UnknowL\commands\admin\SizeCommand;
use UnknowL\commands\admin\SpyCommand;
use UnknowL\commands\admin\TpAllCommand;
use UnknowL\commands\casino\CasinoCommand;
use UnknowL\commands\CommandManager;
use UnknowL\commands\kit\KitCommand;
use UnknowL\commands\market\MarketCommand;
use UnknowL\commands\money\MoneyCommand;
use UnknowL\commands\rank\RankCommand;
use UnknowL\commands\settings\SettingsCommand;
use UnknowL\commands\shop\ShopCommand;
use UnknowL\commands\vip\CraftCommand;
use UnknowL\commands\vip\EcCommand;
use UnknowL\commands\vip\NearCommand;
use UnknowL\commands\vip\RenameCommand;
use UnknowL\commands\vip\RepairCommand;
use UnknowL\commands\warps\AreneCommand;
use UnknowL\commands\warps\FarmZoneCommand;
use UnknowL\commands\warps\LobbyCommand;
use UnknowL\commands\warps\PurifCommand;
use UnknowL\commands\warps\SpawnCommand;
use UnknowL\commands\warps\TutoCommand;
use UnknowL\entities\MinageNPC;
use UnknowL\events\BounceBlock;
use UnknowL\handlers\Handler;
use UnknowL\items\ArcPunch;
use UnknowL\items\Gapple;
use UnknowL\items\Soup;
use UnknowL\lib\commando\exception\HookAlreadyRegistered;
use UnknowL\lib\commando\PacketHooker;
use UnknowL\lib\libasynql\DataConnector;
use UnknowL\lib\ref\libNpcDialogue\libNpcDialogue;
use UnknowL\Linesia;
use UnknowL\listener\PacketListener;
use UnknowL\listener\PlayerListener;
use UnknowL\task\ClearlagTask;
use UnknowL\task\PurifTask;

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
        $this->worlds();
        $this->loadEntities();

        Server::getInstance()->getNetwork()->setName("§dLinesia §5V8");

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
        $pluginManager->registerEvents(new Gapple(), $this);
        $pluginManager->registerEvents(new ArcPunch(), $this);
        $pluginManager->registerEvents(new Soup(), $this);
        $pluginManager->registerEvents(new BounceBlock(), $this);
        $pluginManager->registerEvents(new MineAPI(), $this);
        //$pluginManager->registerEvents(new CpsAPI(), $this);
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

        libNpcDialogue::register($this);
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

    private function worlds(): void {
        foreach(array_diff(scandir($this->getServer()->getDataPath() . "worlds"), ["..", "."]) as $level) {
            $this->getServer()->getWorldManager()->loadWorld($level);
        }

        foreach ($this->getServer()->getWorldManager()->getWorlds() as $world) {
            $world->setTime(World::TIME_DAY);
            $world->stopTime();
        }

    }

	private function loadCommands(): void
	{
		Server::getInstance()->getCommandMap()->register("", new CasinoCommand());
		Server::getInstance()->getCommandMap()->register("", new KitCommand());
		Server::getInstance()->getCommandMap()->register("", new MoneyCommand());
		Server::getInstance()->getCommandMap()->register("", new ShopCommand());
		Server::getInstance()->getCommandMap()->register("", new MarketCommand());
		Server::getInstance()->getCommandMap()->register("", new RankCommand());
        Server::getInstance()->getCommandMap()->register("", new SettingsCommand());

        //Warps
        Server::getInstance()->getCommandMap()->register("", new AreneCommand());
        Server::getInstance()->getCommandMap()->register("", new FarmZoneCommand());
        Server::getInstance()->getCommandMap()->register("", new SpawnCommand());
        Server::getInstance()->getCommandMap()->register("", new LobbyCommand());
        Server::getInstance()->getCommandMap()->register("", new PurifCommand());
        Server::getInstance()->getCommandMap()->register("", new TutoCommand());

        //vip
        Server::getInstance()->getCommandMap()->register("", new CraftCommand());
        Server::getInstance()->getCommandMap()->register("", new EcCommand());
        Server::getInstance()->getCommandMap()->register("", new NearCommand());
        Server::getInstance()->getCommandMap()->register("", new RenameCommand());
        Server::getInstance()->getCommandMap()->register("", new RepairCommand());

        //admin
        Server::getInstance()->getCommandMap()->register("", new ClearLaggCommand());
        Server::getInstance()->getCommandMap()->register("", new GiveAllCommand());
        Server::getInstance()->getCommandMap()->register("", new TpAllCommand());
        Server::getInstance()->getCommandMap()->register("", new IdCommand());
        Server::getInstance()->getCommandMap()->register("", new SayCommand());
        Server::getInstance()->getCommandMap()->register("", new SizeCommand());
        Server::getInstance()->getCommandMap()->register("", new SpyCommand());
        Server::getInstance()->getCommandMap()->register("", new MinageNPCCommand());

	}

	private function loadTask(): void
	{
		Linesia::getInstance()->getScheduler()->scheduleRepeatingTask(new ClearlagTask(), 20 * 60 * 5);
        Linesia::getInstance()->getScheduler()->scheduleRepeatingTask(new PurifTask(), 20 * 30);
	}

    private function loadEntities(): void
    {
        EntityFactory::getInstance()->register(MinageNPC::class, function (World $world, CompoundTag $nbt): MinageNPC {
            return new MinageNPC(EntityDataHelper::parseLocation($nbt, $world), $nbt);
        }, ['MinageNPC']);
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