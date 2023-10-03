<?php

namespace UnknowL\trait;

use pocketmine\command\defaults\SayCommand;
use pocketmine\crafting\ExactRecipeIngredient;
use pocketmine\crafting\ShapedRecipe;
use pocketmine\entity\EntityDataHelper;
use pocketmine\entity\EntityFactory;
use pocketmine\item\Item;
use pocketmine\item\StringToItemParser;
use pocketmine\nbt\tag\CompoundTag;
use pocketmine\network\mcpe\protocol\types\entity\EntityIds;
use pocketmine\scheduler\ClosureTask;
use pocketmine\Server;
use pocketmine\utils\Config;
use pocketmine\utils\TextFormat;
use pocketmine\world\format\io\GlobalItemDataHandlers;
use pocketmine\world\World;
use UnknowL\api\MineAPI;
use UnknowL\api\SettingsAPI;
use UnknowL\blocks\BounceBlock;
use UnknowL\commands\admin\ClearLaggCommand;
use UnknowL\commands\admin\GiveAllCommand;
use UnknowL\commands\admin\IdCommand;
use UnknowL\commands\admin\MinageNPCCommand;
use UnknowL\commands\admin\SizeCommand;
use UnknowL\commands\admin\SpyCommand;
use UnknowL\commands\admin\TpAllCommand;
use UnknowL\commands\boss\Boss;
use UnknowL\commands\box\BoxCommand;
use UnknowL\commands\casino\CasinoCommand;
use UnknowL\commands\CommandManager;
use UnknowL\commands\default\NvCommand;
use UnknowL\commands\dual\DualCommand;
use UnknowL\commands\kit\KitCommand;
use UnknowL\commands\market\MarketCommand;
use UnknowL\commands\money\MoneyCommand;
use UnknowL\commands\rank\RankCommand;
use UnknowL\commands\settings\SettingsCommand;
use UnknowL\commands\shop\ShopCommand;
use UnknowL\commands\stat\StatCommand;
use UnknowL\commands\stat\TopCommand;
use UnknowL\commands\tag\TagCommand;
use UnknowL\commands\vip\CraftCommand;
use UnknowL\commands\vip\EcCommand;
use UnknowL\commands\vip\NearCommand;
use UnknowL\commands\vip\RenameCommand;
use UnknowL\commands\vip\RepairAllCommand;
use UnknowL\commands\vip\RepairCommand;
use UnknowL\commands\vote\VoteCommand;
use UnknowL\commands\warps\AreneCommand;
use UnknowL\commands\warps\FarmZoneCommand;
use UnknowL\commands\warps\LobbyCommand;
use UnknowL\commands\warps\PurifCommand;
use UnknowL\commands\warps\SpawnCommand;
use UnknowL\commands\warps\TutoCommand;
use UnknowL\entities\FloatingText;
use UnknowL\entities\MinageNPC;
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
use UnknowL\task\ChatGameTask;
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
		$this->worlds();
		$this->loadManager();
		$this->loadLib();
		$this->loadResources();
		$this->loadCommands();
		$this->loadCrafts();
		$this->loadListeners();
		$this->loadArmors();
		$this->loadItems();
		$this->loadSwords();
		$this->loadTask();
		$this->loadFolder();
		$this->loadApi();
        $this->loadEntities();

        Server::getInstance()->getNetwork()->setName("§dLinesia §5V8");

		Linesia::getInstance()->getLogger()->notice("§a Activation du core Faction");
	}

	final public function saveAll(): void
	{
		Linesia::getInstance()->getConfig()->set('voteParty', VoteCommand::getInstance()->getVoteParty());
	}
	
	private function loadListeners(): void 
	{
		$pluginManager = Linesia::getInstance()->getServer()->getPluginManager();
		$pluginManager->registerEvents(new PlayerListener(), $this);
		$pluginManager->registerEvents(new PacketListener(), $this);
        $pluginManager->registerEvents(new Gapple(), $this);
        $pluginManager->registerEvents(new ArcPunch(), $this);
        $pluginManager->registerEvents(new Soup(), $this);
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
		$commandMap = Server::getInstance()->getCommandMap();
		
		$commandMap->register("", new CasinoCommand());
		//$commandMap->register("", new DualCommand());
		$commandMap->register("", new KitCommand());
		$commandMap->register("", new MarketCommand());
		$commandMap->register("", new MoneyCommand());
		$commandMap->register("", new RankCommand());
		$commandMap->register("", new DualCommand());
		$commandMap->register("", new TopCommand());
		$commandMap->register("", new NvCommand());
		$commandMap->register("", new VoteCommand());
		$commandMap->register("", new TagCommand());
		$commandMap->register("", new SettingsCommand());
		$commandMap->register("", new ShopCommand());
		$commandMap->register("", new StatCommand());

        //Warps
        $commandMap->register("", new AreneCommand());
        $commandMap->register("", new FarmZoneCommand());
        $commandMap->register("", new SpawnCommand());
        $commandMap->register("", new LobbyCommand());
        $commandMap->register("", new PurifCommand());
        $commandMap->register("", new TutoCommand());

        //vip
        $commandMap->register("", new CraftCommand());
        $commandMap->register("", new EcCommand());
        $commandMap->register("", new NearCommand());
        $commandMap->register("", new RenameCommand());
        $commandMap->register("", new RepairCommand());
        $commandMap->register("", new RepairAllCommand());

        //admin
        $commandMap->register("", new ClearLaggCommand());
        $commandMap->register("", new GiveAllCommand());
        $commandMap->register("", new TpAllCommand());
        $commandMap->register("", new IdCommand());
        $commandMap->register("", new SayCommand());
		$this->getServer()->getCommandMap()->register("", new Boss());
		$commandMap->register('', new BoxCommand());
        $commandMap->register("", new SizeCommand());
        $commandMap->register("", new SpyCommand());
        $commandMap->register("", new MinageNPCCommand());

	}

	private function loadTask(): void
	{
		Linesia::getInstance()->getScheduler()->scheduleRepeatingTask(new ClearlagTask(), 1);
        Linesia::getInstance()->getScheduler()->scheduleRepeatingTask(new PurifTask(), 20 * 20);
		Linesia::getInstance()->getScheduler()->scheduleRepeatingTask(ChatGameTask::getInstance(), 630 * 20);
	}

	private function clearEntities(): void
	{
		foreach (Server::getInstance()->getWorldManager()->getWorlds() as $world)
		{
			foreach ($world->getEntities() as $entity)
			{
				if ($entity instanceof FloatingText)
				{
					if (!$entity->isClosed())
					{
						$entity->flagForDespawn();
					}
				}
			}
		}
	}

    private function loadEntities(): void
    {
		$this->clearEntities();

        EntityFactory::getInstance()->register(MinageNPC::class, function (World $world, CompoundTag $nbt): MinageNPC {
            return new MinageNPC(EntityDataHelper::parseLocation($nbt, $world), $nbt);
        }, ['MinageNPC']);

		EntityFactory::getInstance()->register(FloatingText::class, function(World $world, CompoundTag $nbt) : FloatingText{
			return new FloatingText(EntityDataHelper::parseLocation($nbt, $world), $nbt);
		}, [EntityIds::FALLING_BLOCK]);
		/*$topStats = Handler::OFFLINEDATA()->getTopStats();

		$format = sprintf("Classement de Linésia: \n kills: %d \n death: %d \n killstreak: %.2f \n
		 kd: %d \n Temps de jeu: %d \n Blocks minés: %s \n Blocks Posé: %s", array_values($topStats["kill"]), array_keys($topStats["kill"])[0], $topStats["death"], $topStats["killstreak"],
			$topStats["kd"], $topStats["gametime"], $topStats["blockmined"], $topStats["blockposed"]);
		$entity = new FloatingText(new Location(70, 50, 89, Server::getInstance()->getWorldManager()->getWorldByName("linesia"), 0.0, 0.0));
		$entity->setText($format);
		$entity->spawnToAll();*/
	}

	private function loadArmors(): void
	{
		/*CustomiesItemFactory::getInstance()->registerItem(AmethystHelmet::class, "minecraft:amethyste_helmet", "Casque en Amethyste");
		CustomiesItemFactory::getInstance()->registerItem(AmethystChestplate::class, "minecraft:amethyste_chestplate", "Plastron en Amethyste");
		CustomiesItemFactory::getInstance()->registerItem(AmethystLeggings::class, "minecraft:amethyste_leggings", "Jambières en Amethyste");
		CustomiesItemFactory::getInstance()->registerItem(AmethystBoots::class, "minecraft:amethyste_boots", "Bottes en Amethyste");

		CustomiesItemFactory::getInstance()->registerItem(RubisHelmet::class, "minecraft:rubis_helmet", "Casque en Rubis");
		CustomiesItemFactory::getInstance()->registerItem(RubisChestplate::class, "minecraft:rubis_chestplate", "Plastron en Rubis");
		CustomiesItemFactory::getInstance()->registerItem(RubisLeggings::class, "minecraft:rubis_leggings", "Jambières en Rubis");
		CustomiesItemFactory::getInstance()->registerItem(RubisBoots::class, "minecraft:rubis_boots", "Bottes en Rubis");

		CustomiesItemFactory::getInstance()->registerItem(OnixHelmet::class, "minecraft:onix_helmet", "Casque en Onix");
		CustomiesItemFactory::getInstance()->registerItem(OnixChestplate::class, "minecraft:onix_chestplate", "Plastron en Onix");
		CustomiesItemFactory::getInstance()->registerItem(OnixLeggings::class, "minecraft:onix_leggings", "Jambières en Onix");
		CustomiesItemFactory::getInstance()->registerItem(OnixBoots::class, "minecraft:onix_boots", "Bottes en Onix");*/
	}

	private function loadItems(): void
	{
		/*CustomiesItemFactory::getInstance()->registerItem(AmethysteIngot::class, "minecraft:amethyste_ingot", "Amethyste");
		CustomiesItemFactory::getInstance()->registerItem(RubisFragement::class, "minecraft:rubis_fragement", "Fragement de Rubis");
		CustomiesItemFactory::getInstance()->registerItem(RubisIngot::class, "minecraft:rubis_ingot", "Lingot de Rubis");
		CustomiesItemFactory::getInstance()->registerItem(OnixBrisure::class, "minecraft:onix_brisure", "Brisure d'Onix");
		CustomiesItemFactory::getInstance()->registerItem(OnixFragement::class, "minecraft:onix_fragement", "Fragement d'Onix");
		CustomiesItemFactory::getInstance()->registerItem(OnixIngot::class, "minecraft:onix_ingot", "Lingot d'Onix");*/
	}

	private function loadSwords(): void
	{
	//	CustomiesItemFactory::getInstance()->registerItem(AmethystSword::class, "minecraft:amethyste_sword", "Epée en Amethyste");
		/*CustomiesItemFactory::getInstance()->registerItem(RubisSword::class, "minecraft:rubis_sword", "Epée en Rubis");
		CustomiesItemFactory::getInstance()->registerItem(OnixSword::class, "minecraft:onix_sword", "Epée en Onix");
		CustomiesItemFactory::getInstance()->registerItem(GodSword::class, "minecraft:god_sword", "Epée en God");*/
	}
	private function loadFolder(): void
	{
		@mkdir(Linesia::getInstance()->getDataFolder().DIRECTORY_SEPARATOR."data");
		@mkdir(Linesia::getInstance()->getDataFolder().DIRECTORY_SEPARATOR."data".DIRECTORY_SEPARATOR."shop");
	}

	private function loadApi(): void
	{
		new SettingsAPI();
	}

	public function loadCrafts(): void
	{
		$config = new Config($this->getDataFolder()."CustomCraft/Config.json", Config::JSON);
		$craftingManager = Server::getInstance()->getCraftingManager();
		$removedRecipes = [];
		foreach ($config->getNested('blacklist.crafting') as $blackListItem) {
			$ex = explode(":", $blackListItem);
			$item = GlobalItemDataHandlers::getDeserializer()->deserializeStack(GlobalItemDataHandlers::getUpgrader()->upgradeItemTypeDataInt($ex[0], $ex[1], 1, null));
			$recipe = $craftingManager->matchRecipeByOutputs([$item]);

			if ($recipe instanceof  \Generator) {$recipe = $recipe->current();}

			if (!isset($removedRecipes[$item->getName()]))
			{
				$property = new \ReflectionProperty($craftingManager, 'shapedRecipes');
				$property->setAccessible(true);
				$value = $property->getValue($craftingManager);
				$property->setValue($craftingManager, $value);
				$property = new \ReflectionProperty($craftingManager, 'craftingRecipeIndex');
				$value = $property->getValue($craftingManager);
				unset($value[array_search($recipe, $value, true)]);
				$property->setValue($craftingManager, $value);
				$removedRecipes[$item->getName()] = true;
				Server::getInstance()->getLogger()->notice("§a §l[§bCRAFT§a] §cRemoved recipe: ". $item->getName());
			}
		}

		$factory = StringToItemParser::getInstance();
		foreach(array_diff(scandir($this->getDataFolder()."/CustomCraft/Crafting_table"), ["..", "."]) as $path){
			$check = explode(".", $path);
			if($this->checkFileExtension($path)){
				$config = new Config($this->getDataFolder()."/CustomCraft/Crafting_table/{$path}", Config::JSON);
				$v = $config->getAll();
					if($this->checkCraftingData($v)){
						$recipes = [];

						foreach($v['key'] as $k => $v2){
							if(!$factory->parse($v2['item']) instanceof Item) return;
							$recipes[$k] = new ExactRecipeIngredient($factory->parse($v2['item']));
						}

						$count = 1;
						if(isset($v['result']['count']) && is_numeric($v['result']['count']) && $v['result']['count'] > 0)$count = (int)$v['result']['count'];

						$result = $factory->parse($v['result']['item'])->setCount($count);

						if(!$result instanceof Item) return;

						if(isset($v['result']['name']) && $v['result']['name'] !== "")$result->setCustomName("§r".TextFormat::colorize($v['result']['name']));

						$craftingManager->registerShapedRecipe(new ShapedRecipe($v['pattern'], $recipes, [$result]));
						Server::getInstance()->getLogger()->notice("§a §l[§bCRAFT§a] §aRegistered recipe: ". $path);
					}else{
						if($config->get("logger", false))$this->getLogger()->error("Invalid data entered in Crafting_table/{$path}");
					}
				}else{
				if($config->get("logger", false))$this->getLogger()->error("Crafting_table/{$path} must be a JSON file");
			}
		}
	}

	public function getEnchantmentByName(string $ench): int {
		return isset($this->enchantment[$ench]) === true ? $this->enchantment[$ench] : -1;
	}


	private function checkCraftingData($tag): bool {
		$v = false;
		$keys = [];
		if(isset($tag['pattern']) && is_array($tag['pattern']) && isset($tag['key']) && is_array($tag['key']) && isset($tag['result']) && is_array($tag['result'])){
			$height = count($tag['pattern']);
			if($height > 3 || $height <= 0){
				return false;
			}
			$shape = array_values($tag['pattern']);
			$width = strlen($shape[0]);
			if($width > 3 || $width <= 0){
				return false;
			}
			foreach($tag['key'] as $k => $value){
				if($this->checkData($value))$keys[$k] = $k;
			}
			foreach($shape as $n => $row){
				if(strlen($row) !== $width){
					return false;
				}
				for($x = 0; $x < $width; ++$x){
					if($row[$x] !== ' ' && !isset($keys[$row[$x]])){
						return false;
					}
				}
			}
			foreach($keys as $char => $l){
				if(strpos(implode($shape), $char) === false){
					return false;
				}
				$v = true;
			}
			if(!$this->checkData($tag['result']))$v = false;
		}
		return $v;
	}

	private function checkFileExtension(string $path):bool{
		$extension = explode(".", $path);
		return (isset($extension[1]) && $extension[1] === "json");
	}

	private function checkData($tag): bool {
		if(is_array($tag) && isset($tag['item']) && is_string($tag['item']))return true;
		return false;
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