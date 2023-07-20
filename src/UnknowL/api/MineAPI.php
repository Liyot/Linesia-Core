<?php

namespace UnknowL\api;

use pocketmine\block\VanillaBlocks;
use pocketmine\event\block\BlockBreakEvent;
use pocketmine\event\Listener;
use pocketmine\event\player\PlayerMoveEvent;
use pocketmine\item\VanillaItems;
use pocketmine\math\AxisAlignedBB;
use Job\Session;
use pocketmine\math\Vector3;
use pocketmine\player\Player;
use pocketmine\scheduler\ClosureTask;
use pocketmine\Server;
use pocketmine\utils\Config;
use pocketmine\utils\SingletonTrait;
use pocketmine\world\particle\HappyVillagerParticle;
use pocketmine\world\sound\XpCollectSound;
use UnknowL\Linesia;

class MineAPI implements Listener
{
	use SingletonTrait;

	private Config $config;

	public function __construct()
	{
		$this->config = new Config(Linesia::getInstance()->getDataFolder() . "data.json", Config::JSON);
		self::setInstance($this);
	}

    /**
     * @param BlockBreakEvent $event
     * @return void
     */
    public function onBlockBreakEvent(BlockBreakEvent $event): void
    {
        $player = $event->getPlayer();
        $block = $event->getBlock();
        $world = $player->getWorld();

        if ($world === Server::getInstance()->getWorldManager()->getWorldByName("farmzone")) {

            if ($block->getTypeId() !== VanillaBlocks::GOLD_ORE()->getTypeId() or $block->getTypeId() !== VanillaBlocks::DIAMOND_ORE()->getTypeId() or $block->getTypeId() !== VanillaBlocks::EMERALD_ORE()->getTypeId() or $block->getTypeId() !== VanillaBlocks::NETHER_GOLD_ORE()->getTypeId() or $block->getTypeId() !== VanillaBlocks::NETHER_QUARTZ_ORE()->getTypeId())
			{

                $event->cancel();
            }
        }

        if ($world === Server::getInstance()->getWorldManager()->getWorldByName("farmzone"))
		{

            if ($block->getTypeId() == VanillaBlocks::GOLD_ORE()->getTypeId() or $block->getTypeId() == VanillaBlocks::DIAMOND_ORE()->getTypeId() or $block->getTypeId() == VanillaBlocks::EMERALD_ORE()->getTypeId() or $block->getTypeId() == VanillaBlocks::NETHER_GOLD_ORE()->getTypeId() or $block->getTypeId() == VanillaBlocks::NETHER_QUARTZ_ORE()->getTypeId()) {

				$plugin = Server::getInstance()->getPluginManager()->getPlugin('mJob');
				$session = Session::get($player);
				$plugin->addXp($session, ["item", "break", $block->asItem()]);

				$world->dropItem($block->getPosition(),match ($block->getTypeId())
				{
					VanillaBlocks::EMERALD_ORE()->getTypeId() => VanillaItems::AMETHYST_SHARD(),
					VanillaBlocks::GOLD_ORE()->getTypeId() => VanillaItems::GOLD_NUGGET(),
					VanillaBlocks::NETHER_GOLD_ORE()->getTypeId() => VanillaItems::GOLD_NUGGET(),
					VanillaBlocks::NETHER_QUARTZ_ORE()->getTypeId() => VanillaItems::PRISMARINE_CRYSTALS(),
					VanillaBlocks::DIAMOND_ORE()->getTypeId() => VanillaItems::DIAMOND(),
					default => VanillaItems::AIR()
				});


                $block->getPosition()->getWorld()->setBlock($block->getPosition(), VanillaBlocks::BEDROCK());

                //PARTICULE et SOUND (lag)
                $event->getPlayer()->getWorld()->addParticle($event->getBlock()->getPosition()->add(0.5, 1, 0.5), new HappyVillagerParticle());
                $event->getPlayer()->broadcastSound(new XpCollectSound());

                Linesia::getInstance()->getScheduler()->scheduleDelayedTask(new ClosureTask(function () use ($block) {

                    $block->getPosition()->getWorld()->setBlock($block->getPosition(), $block);

                }), 20 * 5);

            }
        }
    }

    public function onMove(PlayerMoveEvent $event): void
    {

        if ($event->getFrom()->asPosition()->equals($event->getTo()->asPosition())) return;
        if ($event->getFrom()->getWorld()->getFolderName() !== 'farmzone') return;

		if (!$this->canAccess($event->getPlayer()) and $this->isInArea($event->getPlayer(), ["219", "0", "306"], ["93", "150", "166"])) {
            $event->getPlayer()->sendActionBarMessage('§cVous ne pouvez pas accéder à cette mine.');
            $event->cancel();
        }
    }

    public function canAccess(Player $player): bool
    {
        return $this->getQuestData()->getNested("{$player->getName()}.quest", 1) === 6;
    }

	public static function isInArea(Player $player, array $pos, array $pos_)
	{
		if (($player->getPosition()->x >= min($pos[0], $pos_[0])) and ($player->getPosition()->x <= max($pos[0], $pos_[0])) and
			($player->getPosition()->y >= min($pos[1], $pos_[1])) and ($player->getPosition()->y <= max($pos[1], $pos_[1])) and
			($player->getPosition()->z >= min($pos[2], $pos_[2])) and ($player->getPosition()->z <= max($pos[2], $pos_[2]))) {
			return true;
		}
		return false;
	}

	public function getQuestData(): Config
	{
		return $this->config;
	}
}