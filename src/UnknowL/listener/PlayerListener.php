<?php

namespace UnknowL\listener;

use pocketmine\block\Anvil;
use pocketmine\block\EnchantingTable;
use pocketmine\block\EnderChest;
use pocketmine\block\inventory\CraftingTableInventory;
use pocketmine\block\ItemFrame;
use pocketmine\block\MonsterSpawner;
use pocketmine\block\VanillaBlocks;
use pocketmine\console\ConsoleCommandSender;
use pocketmine\crafting\CraftingGrid;
use pocketmine\data\bedrock\item\SavedItemData;
use pocketmine\data\bedrock\item\SavedItemStackData;
use pocketmine\data\bedrock\PotionTypeIdMap;
use pocketmine\data\bedrock\PotionTypeIds;
use pocketmine\entity\Zombie;
use pocketmine\event\block\BlockBreakEvent;
use pocketmine\event\block\BlockPlaceEvent;
use pocketmine\event\entity\EntityDamageByEntityEvent;
use pocketmine\event\entity\EntityDamageEvent;
use pocketmine\event\entity\EntityItemPickupEvent;
use pocketmine\event\entity\EntityTrampleFarmlandEvent;
use pocketmine\event\entity\ItemSpawnEvent;
use pocketmine\event\Event;
use pocketmine\event\inventory\CraftItemEvent;
use pocketmine\event\inventory\InventoryTransactionEvent;
use pocketmine\event\Listener;
use pocketmine\event\player\PlayerChatEvent;
use pocketmine\event\player\PlayerCreationEvent;
use pocketmine\event\player\PlayerDeathEvent;
use pocketmine\event\player\PlayerExhaustEvent;
use pocketmine\event\player\PlayerInteractEvent;
use pocketmine\event\player\PlayerJoinEvent;
use pocketmine\event\player\PlayerJumpEvent;
use pocketmine\event\player\PlayerQuitEvent;
use pocketmine\event\player\PlayerToggleSneakEvent;
use pocketmine\event\server\CommandEvent;
use pocketmine\event\world\ChunkUnloadEvent;
use pocketmine\inventory\CreativeInventory;
use pocketmine\inventory\PlayerOffHandInventory;
use pocketmine\inventory\transaction\action\SlotChangeAction;
use pocketmine\item\Durable;
use pocketmine\item\Item;
use pocketmine\item\StringToItemParser;
use pocketmine\item\VanillaItems;
use pocketmine\lang\Translatable;
use pocketmine\math\Vector3;
use pocketmine\player\chat\ChatFormatter;
use pocketmine\player\Player;
use pocketmine\scheduler\ClosureTask;
use pocketmine\Server;
use pocketmine\world\Position;
use pocketmine\world\WorldManager;
use UnknowL\api\CombatLoggerManager;
use UnknowL\api\ElevatorAPI;
use UnknowL\api\KillDeathManager;
use UnknowL\api\ScoreBoardAPI;
use UnknowL\api\SettingsAPI;
use UnknowL\blocks\AnvilUI;
use UnknowL\blocks\EnchantUI;
use UnknowL\commands\admin\SpyCommand;
use UnknowL\events\CooldownExpireEvent;
use UnknowL\form\EnderChestForm;
use UnknowL\handlers\dataTypes\PlayerCooldown;
use UnknowL\handlers\Handler;
use UnknowL\Linesia;
use UnknowL\player\LinesiaPlayer;
use UnknowL\player\manager\StatManager;
use UnknowL\task\ChatGameTask;
use UnknowL\utils\ItemUtils;
use UnknowL\utils\PathLoader;

final class PlayerListener implements Listener
{

    public static array $cooldown = [];
    public static array $messageLog = [];
    public static array $time = [];
    private $blockedWords = ["hitler", "ez", "ezz", "ezzz", "enculer", "nul", "fdp", "pute", "tg", "ntm", "grisollet", "remy", "ftg", "gueule", "bordel", "putain", "merde", "con", "connard", "batard", "cul", "bite", "couille", "clc", "csc", "enfoiré", "enfoire", "petasse", "abruti", "bouffon", "veski"];

    private const EFFECT_MAX_DURATION = 2147483647;

	/**@var SimpleSharedListener[] $sharedListeners*/
	public array $sharedListeners = [];

	private array $items = ["minecraft:stone_sword" => 2000, "minecraft:golden_sword" => 2500, "minecraft:iron_sword" => 3000];

	public function __construct()
	{
		$this->sharedListeners[] = new SimpleSharedListener($this, ChatGameTask::getInstance());
	}

	private function sharedExecution(Event $event): void
	{
		array_map(fn(SimpleSharedListener $sharedListener) => $sharedListener->onSharedEvent($event), $this->sharedListeners);
	}

    public function onCreation(PlayerCreationEvent $event)
    {
		$event->setPlayerClass(LinesiaPlayer::class);
    }

    public function onJoin(PlayerJoinEvent $event): void {

		/**@var LinesiaPlayer $player*/
       $player = $event->getPlayer();
        $name = $player->getName();

        //SETTINGS
        SettingsAPI::createPlayer($player);

        if($player->hasPlayedBefore()) {
            $event->setJoinMessage("§a[+] $name");
			$player->getStatManager()->onConnexion();
        } else {
            $event->setJoinMessage("§d$name §fnous a rejoints pour la première fois, bienvenue à lui !");
			$player->getStatManager()->onFirstConnexion();
            //$sender->teleport(new Position(300.5, 6, 305.5, $sender->getServer()->getWorldManager()->getWorldByName("tuto")));
        }


		//CPS
        //Main::$instance->clicks[$event->getPlayer()->getName()] = [];

        //EFFECTARMOR
     /*   foreach ($sender->getArmorInventory()->getContents() as $targetItem) {
            if ($targetItem instanceof Armor) {
                $slot = $targetItem->getArmorSlot();
                $sourceItem = $sender->getArmorInventory()->getItem($slot);

                $this->addEffects($sender, $sourceItem, $targetItem);
            } else {
                if ($targetItem->getTypeId() == VanillaBlocks::AIR()->getTypeId()) {
                    $this->addEffects($sender, ItemFactory::air(), $targetItem);
                }
            }
        }

        $sender->getArmorInventory()->getListeners()->add(new CallbackInventoryListener(function(Inventory $inventory, int $slot, Item $oldItem) : void{
            if ($inventory instanceof ArmorInventory) {
                $targetItem = $inventory->getItem($slot);
                $this->addEffects($inventory->getHolder(), $oldItem, $targetItem);
            }
        },  null));*/

        //SCORDBOARD
       ScoreBoardAPI::sendScoreboard($player);
        ScoreBoardAPI::updateServer();
        self::$time[$player->getName()] = time();
    }

	public function onQuit(PlayerQuitEvent $event): void {

        $player = $event->getPlayer();
        $playerName = $player->getName();

        //MSG LEAVE
        $event->setQuitMessage("§c[-] $playerName");

        //SCORDBOARD
        ScoreBoardAPI::updateServer(true);

        //CPS
        if(isset($this->clicks[$event->getPlayer()->getName()])) unset($this->clicks[$event->getPlayer()->getName()]);

        //COMBAT KILL
        if (isset(CombatLoggerManager::$isLogged[$playerName])) {
            $player->kill();

            Linesia::getInstance()->getScheduler()->scheduleDelayedTask(new ClosureTask(function () use ($event, $player, $playerName): void {

                if(isset(CombatLoggerManager::$isLogged[$playerName])) {
                    $handler = CombatLoggerManager::$isLogged[$playerName]["task"];
                    $handler->getTask()->getHandler()->cancel();
					var_dump('as');
                    unset(CombatLoggerManager::$isLogged[$playerName]);
                }
            }), 1);
        }
    }

    public function onCooldownExpire(CooldownExpireEvent $event)
    {
        $cooldown = $event->getCooldown();
        $player = $event->getPlayer();
        if(!is_null($player))
        {
            /**@var PlayerCooldown $cooldown*/
			switch ($cooldown->getPath())
            {
                case PathLoader::PATH_RANK_CACHE:

            }
        }
    }

    public function onInventoryTransaction(InventoryTransactionEvent $event) : void
    {
        $transaction = $event->getTransaction();
        $player = $transaction->getSource();
        foreach ($transaction->getActions() as $action) {
            if ($action instanceof SlotChangeAction) {
                if ($action->getInventory() instanceof PlayerOffHandInventory) {
                    if ($action->getTargetItem() instanceof (VanillaItems::ARROW())) {
                        $event->cancel();
                    }
                }
            }
        }
    }

    public function onDamage(EntityDamageEvent $event)
    {
        //NOFALL
        $cause = $event->getCause();
		if ($cause === EntityDamageEvent::CAUSE_DROWNING && $event->getEntity() instanceof Zombie)
		{
			$event->cancel();
		}

        if ($cause === EntityDamageEvent::CAUSE_FALL) {
            $event->cancel();
        }
    }

	public function onCommand(CommandEvent $event)
	{

		$player = $event->getSender();
		$message = $event->getCommand();
		$playerName = $player->getName();
		if(!$player instanceof LinesiaPlayer) return;
		if (!$player->getActiveInteraction(LinesiaPlayer::INTERACTION_COMMAND)) $event->cancel();

		//COMBAT LOGGER
		if (!in_array($message, ["mute", "ban", "gm1", "unmute", "jail"]) && isset(CombatLoggerManager::$isLogged[$player->getName()])) {

			$player->sendMessage("§cVous ne pouvez pas effectuer de commande en combat !");
			$event->cancel();
		}

		//SPY
		Server::getInstance()->getLogger()->info("{$playerName} -> {$message}");

		if (!empty(SpyCommand::$spy)) {
			foreach (SpyCommand::$spy as $name) {
				$player = Server::getInstance()->getPlayerExact($name);
				if ($player instanceof Player) {
					$player->sendMessage("§c{$player->getName()}§7 -> §c{$message}");
				}
			}
		}
	}

    public function onCombatLogger(EntityDamageByEntityEvent $event): void {

        $victim = $event->getEntity();
        $damager = $event->getDamager();

        //KB
        $event->setKnockBack(0.354);
        $event->setAttackCooldown(7); //7.5


        //COMBAT LOGGER
        Linesia::getInstance()->getScheduler()->scheduleDelayedTask(new ClosureTask(function () use ($event, $victim, $damager) : void {

            if($victim instanceof Player && $damager instanceof Player && !$event->isCancelled()) {

                if(!$victim->isConnected() || !$damager->isConnected())
                    return;

                if(!isset(CombatLoggerManager::$isLogged[$victim->getName()]))
                    $victim->sendMessage("§cVous entrez en combat, merci de ne pas déconnecter !");

                if(!isset(CombatLoggerManager::$isLogged[$damager->getName()]))
                    $damager->sendMessage("§cVous entrez en combat, merci de ne pas déconnecter !");

                //MAXITEM
				/**@var Item[] $maxItems*/
                $maxItems = [
					64 => VanillaItems::SLIMEBALL(),
					6 => VanillaItems::GOLDEN_APPLE(),
					16 => VanillaItems::ENDER_PEARL(),

                ];

				/**@var Player $player*/
                foreach (array($victim, $damager) as $player){
					$count = [];
                        foreach ($player->getInventory()->getContents() as $slot => $invItem) {
							if (in_array($invItem->getVanillaName(), array_map(fn(Item $item) => $item->getVanillaName(), $maxItems)))
							{
								isset($count[$invItem->getVanillaName()]) ? $count[$invItem->getVanillaName()] += $invItem->getCount() : $count[$invItem->getVanillaName()] = $invItem->getCount();
							}
						}
						foreach ($maxItems as $itemCount => $item)
						{
							if (isset($count[$item->getVanillaName()]) && $count[$item->getVanillaName()] > $itemCount)
							{
								$player->getInventory()->removeItem($item->setCount($count[$item->getVanillaName()]));
								$player->getInventory()->addItem($item->setCount($itemCount));
							}
						}
                }

                CombatLoggerManager::updateLog($victim);
                CombatLoggerManager::updateLog($damager);
            }
        }), 2);

        //GOD SWORD
        /*if ($event->isCancelled())
            return;

        if ($damager instanceof Player && $victim instanceof Player) {
            $item = $damager->getInventory()->getItemInHand();
            if ($item->getTypeId() === 965) {
                $percentage = [];
                for ($i = 0; $i <= 6; $i++) {
                    $percentage[$i] = "thunder";
                }
                for ($i = 6; $i <= 100; $i++) {
                    $percentage[$i] = "";
                }
                $rand = mt_rand(0, count($percentage) - 1);
                shuffle($percentage);
                $value = $percentage[$rand];
                if ($value !== "") {
                    $thunder = new LightningBolt($victim->getLocation());
                    $thunder->spawnToAll();
                    $victim->setOnFire(5);
                    /*if (4 < $entity->getHealth()) {
                        $entity->setHealth($entity->getHealth() - 4);
                    }else{
                        $entity->kill();
                        }*/
                /*}
            }
        }*/
    }

    public function onDeath(PlayerDeathEvent $event)
    {
        $player = $event->getEntity();
        $cause = $player->getLastDamageCause();
        $playerName = $player->getName();

        //killmoney
        if ($cause instanceof EntityDamageByEntityEvent) {
            $send = $cause->getDamager();
            if ($send instanceof LinesiaPlayer) {
                if($cause->getCause() === EntityDamageByEntityEvent::CAUSE_ENTITY_ATTACK){

                    $send->getEconomyManager()->add(100);
                    $send->sendMessage("§aTu viens de gagné 100$ grâce à ton kill !");
                }
            }
        }

        //KILL MSG
        if($cause instanceof EntityDamageByEntityEvent) {
            $damager = $cause->getDamager();
        } else {
            $damager = null;
        }

        $event->setDeathMessage("");

        if(!is_null($cause))
            KillDeathManager::sendDeathMessage($player, $cause->getCause(), $damager);

        //COMBATLOGGER
        if (isset(CombatLoggerManager::$isLogged[$playerName])) {

            Linesia::getInstance()->getScheduler()->scheduleDelayedTask(new ClosureTask(function () use ($event, $player, $playerName): void {

                if(isset(CombatLoggerManager::$isLogged[$playerName])) {

                    $handler = CombatLoggerManager::$isLogged[$playerName]["task"];
                    $handler->getTask()->getHandler()->cancel();

                    unset(CombatLoggerManager::$isLogged[$playerName]);
                }

                if($player->isConnected())
                    $player->sendMessage("§aVous n'êtes plus en combat !");

            }), 3);
        }
    }

    public function onMsg(PlayerChatEvent $event)
    {
		/**@var LinesiaPlayer $player*/
        $player = $event->getPlayer();
        $message = $event->getMessage();
        $playerName = $player->getName();

		$this->sharedExecution($event);

       // mdr non if ($player->hasPermission(DefaultPermissions::ROOT_OPERATOR)) return;

        //MINIMUM LETTRE MSG
        if (strlen($message) < 2) {
            $player->sendMessage("§cVeuillez spécifier au minimum 2 caractères !");
            $event->cancel();
            return;
        }

        //COULEUR MSG
        if (preg_match('/§[0-9a-fk-or]/i', $message)) {
            $player->sendMessage("§cLes couleurs sont interdites !");
            $event->cancel();
        }

        //IDEM MSG
        if (isset(self::$messageLog[$player->getName()]) && str_replace(" ", "", $message) === str_replace(" ", "", self::$messageLog[$player->getName()])) {
            $player->sendMessage("§cVeuillez ne pas envoyer le même message !");
            $event->cancel();
            return;
        }

        //MAJ max CHAT
        if (strlen(preg_replace('![^A-Z]+!', '', $message)) > 8) {
            $player->sendMessage("§cVeuillez à ne pas mettre plus de 8 majuscules !");
            $event->cancel();
            return;
        }

        //Mot BLOCKER
        /*foreach ($this->blockedWords as $word) {
            if (stripos($message, $word) !== false) {
                $player->sendMessage("§cLe mot '$word' est interdit !");
                $event->cancel();
                break;
            }
        }*/

        //COOLDOWN CHAT
        if(isset(self::$cooldown[$playerName]) && self::$cooldown[$playerName] > microtime(true)) {
            $player->sendMessage("§cVeuillez ne pas spam !");
            $event->cancel();
            return;
        }

		$event->setMessage($player->getRank()->handleMessage($message, $player));
		$event->setFormatter(new class implements ChatFormatter {
			public function format(string $username, string $message): Translatable|string
			{
				return $message;
			}
		});
        self::$cooldown[$playerName] = microtime(true) + 1;
	}

    //NO HUNGER
    public function NoHunger(PlayerExhaustEvent $event)
    {
        $event->cancel();
    }

    //NO BREAK CULTURE
    public function onFarmLand(EntityTrampleFarmlandEvent $event) {
        $entity = $event->getEntity();
        if ($entity instanceof Player) {
            $event->cancel();
        }
    }

    //AUTO CLEAR
    public function onClear(ItemSpawnEvent $event) {
        $entity = $event->getEntity();
        $entity->setDespawnDelay(20*20);
    }

    //ANIMATION FRAPPE
    /*public function onPacketReceive(DataPacketReceiveEvent $event) {
        $packet = $event->getPacket();
        if ($packet instanceof AnimatePacket) {
            $event->getOrigin()->getPlayer()->getServer()->broadcastPackets($event->getOrigin()->getPlayer()->getViewers(), [$event->getPacket()]);
        }
    }*/

	/**@priority LOWEST*/
    public function onBlockBreak(BlockBreakEvent $event) : void{

        $player = $event->getPlayer();
        $block = $event->getBlock();
		$world = $event->getPlayer()->getWorld();

		if ($event->isCancelled()) return;

		if (Handler::BOX()->testPosition($block->getPosition()))
		{
			$box = Handler::BOX()->getBoxByPosition($block->getPosition());
			if (Server::getInstance()->isOp($player->getName()))  Handler::BOX()->removeBox($box);
		}

        if ($block->getTypeId() == VanillaBlocks::NETHER_WART_BLOCK()->getTypeId()) {
            $rand = mt_rand(0, 250);
            if ($rand === 1) {
                $event->setDrops([VanillaItems::IRON_NUGGET()]);
            } else {
                if ($rand >= 2) {
                    $event->setDrops([VanillaBlocks::NETHER_WART()]);
                }
            }
        }

        if ($block->getTypeId() == VanillaBlocks::EMERALD_ORE()->getTypeId()) {
            $event->setDrops([VanillaItems::AMETHYST_SHARD()]);
        }

        if ($block->getTypeId() == VanillaBlocks::GOLD_ORE()->getTypeId()) {
            $event->setDrops([VanillaItems::GOLD_NUGGET()]);
        }

        if ($block->getTypeId() == VanillaBlocks::NETHER_GOLD_ORE()->getTypeId()) {
            $event->setDrops([VanillaItems::GOLD_NUGGET()]);
        }

        if ($block->getTypeId() == VanillaBlocks::NETHER_QUARTZ_ORE()->getTypeId()) {
            $event->setDrops([VanillaItems::PRISMARINE_CRYSTALS()]);
        }
    }

    public function onBlockPlace(BlockPlaceEvent $event){
		/**@var LinesiaPlayer $player*/
        $player = $event->getPlayer();
		$blocks = $event->getTransaction()->getBlocks();

        foreach ($blocks as $block)
        {
            if ($block instanceof MonsterSpawner)
            {
				if ($block->getPosition()->getWorld()->getFolderName() === "linesia")
				{
					$event->cancel();
				}
				Handler::WORLD()->addSpawner($block->getPosition()->getWorld());
			}
        }

        //CANCEL
        $item = $event->getItem();
        if ($item->getTypeId() === VanillaBlocks::MELON()->getTypeId() or $item->getTypeId() === VanillaBlocks::PUMPKIN()->getTypeId()) {
            $event->cancel();
        }

		if ($item->getNamedTag()->getTag('box', null) !== null)
		{
			Handler::BOX()->getBox($item->getNamedTag()->getString('box'))
				?->place(Position::fromObject($event->getBlockAgainst()->getPosition()->add(0, 1, 0), $player->getWorld()), $player);
		}

		if (!$event->isCancelled())
		{
			$player->getStatManager()->handleEvents(StatManager::TYPE_BLOCK_PLACED);
		}
    }

    public function onInteract(PlayerInteractEvent $event) {
		/**@var LinesiaPlayer $player*/
        $player = $event->getPlayer();
        $block = $event->getBlock();

        //ENDERCHEST MSG
        if($block instanceof EnderChest) {
            $event->cancel();
            $ec = new EnderChestForm($player);
            $ec->open();
        }

        //ANTI ITEM FRAM
        if ($block instanceof ItemFrame && !Server::getInstance()->isOp($player->getName())) {
            $event->cancel();
        }

        //COMBAT BLOCK
        if ($block->getTypeId() === VanillaBlocks::CHEST()->getTypeId() or $block->getTypeId() === VanillaBlocks::ENCHANTING_TABLE()->getTypeId() or $block->getTypeId() === VanillaBlocks::ANVIL()->getTypeId() or $block->getTypeId() === VanillaBlocks::HOPPER()->getTypeId() or $block->getTypeId() === VanillaBlocks::BARREL()->getTypeId()){
            if (isset(CombatLoggerManager::$isLogged[$player->getName()])){
                $event->cancel();
            }
        }

        //TABLE D'ENCHANTE
        if (!$event->isCancelled()) {
            if ($event->getBlock() instanceof EnchantingTable) {
                if ($event->getAction() === PlayerInteractEvent::RIGHT_CLICK_BLOCK) {
                    if (!$event->isCancelled()) {
                        if ($event->getPlayer()->getInventory()->getItemInHand()->getTypeId() !== VanillaItems::AIR()->getTypeId()) {
                            $enchant = new EnchantUI();
                            $enchant->enchantementUi($player);
                        } else $event->getPlayer()->sendMessage("§cVous devez avoir un item dans vos mains !");
                    }
                }
                $event->cancel();
            }
        }

        //ANVILUI
        if (!$event->isCancelled()) {
            if ($event->getBlock() instanceof Anvil) {
                if ($event->getAction() === PlayerInteractEvent::RIGHT_CLICK_BLOCK) {
                    if (!$event->isCancelled()) {
                        if ($event->getPlayer()->getInventory()->getItemInHand()->getTypeId() !== VanillaItems::AIR()->getTypeId()) {
                            $enchant = new AnvilUI();
                            $enchant->Anvil($player);
                        } else $event->getPlayer()->sendMessage("§cVous devez avoir un item dans vos mains !");
                    }
                }
                $event->cancel();
            }
        }

		if (Handler::BOX()->testPosition($event->getBlock()->getPosition(), $player))
		{
			$event->cancel();
		}
    }

    //ELEVATOR
    public function onPlayerJump(PlayerJumpEvent $event): bool {
        $player = $event->getPlayer();
        $level = $player->getWorld();

        if ($level->getBlock($player->getPosition()->subtract(0, 1, 0))->getTypeId() !== VanillaBlocks::END_STONE()->getTypeId()) return false;

        $x = (int)floor($player->getPosition()->getX());
        $y = (int)floor($player->getPosition()->getY());
        $z = (int)floor($player->getPosition()->getZ());
        $maxY = $level->getMaxY();
        $found = false;
        $y++;

        for (; $y <= $maxY; $y++) {
            if ($found = (ElevatorAPI::isElevatorBlock($x, $y, $z, $level) !== null)) {
                break;
            }
        }

        if ($found) {
            if ($player->getPosition()->distance(new Vector3($x + 0.5, $y + 1, $z + 0.5)) <= 25) {
                $player->teleport(new Vector3($x + 0.5, $y + 1, $z + 0.5));
            } else $player->sendMessage("§cVous etes trop loin de l'élévateur");
        } else $player->sendMessage("§cIl n'y pas d'élévateur.");
        return true;
    }

    public function onPlayerToggleSneak(PlayerToggleSneakEvent $event): bool
    {
        $player = $event->getPlayer();
        $level = $player->getWorld();

        if (!$event->isSneaking()) return false;
        if ($level->getBlock($player->getPosition()->subtract(0, 1, 0))->getTypeId() !== VanillaBlocks::END_STONE()->getTypeId()) return false;

        $x = (int)floor($player->getPosition()->getX());
        $y = (int)floor($player->getPosition()->getY()) - 2;
        $z = (int)floor($player->getPosition()->getZ());
        $found = false;
        $y--;

        for (; $y >= 0; $y--) {
            if ($found = (ElevatorAPI::isElevatorBlock($x, $y, $z, $level) !== null)) {
                break;
            }
        }

        if ($found) {
            if ($player->getPosition()->distance(new Vector3($x + 0.5, $y + 1, $z + 0.5)) <= 25) {
                $player->teleport(new Vector3($x + 0.5, $y + 1, $z + 0.5));
            } else $player->sendMessage("§cVous etes trop loin de l'élévateur");
        } else $player->sendMessage("§cIl n'y pas d'élévateur.");
        return true;
    }
}