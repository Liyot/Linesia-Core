<?php

namespace UnknowL\api;

use pocketmine\block\VanillaBlocks;
use pocketmine\event\block\BlockBreakEvent;
use pocketmine\event\Listener;
use pocketmine\event\player\PlayerMoveEvent;
use pocketmine\math\Vector3;
use pocketmine\player\Player;
use pocketmine\scheduler\ClosureTask;
use pocketmine\Server;
use pocketmine\world\particle\HappyVillagerParticle;
use pocketmine\world\sound\XpCollectSound;
use UnknowL\Linesia;

class MineAPI implements Listener
{

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

            if ($block->getTypeId() != VanillaBlocks::GOLD_ORE()->getTypeId() or $block->getTypeId() != VanillaBlocks::DIAMOND_ORE()->getTypeId() or $block->getTypeId() != VanillaBlocks::EMERALD_ORE()->getTypeId() or $block->getTypeId() != VanillaBlocks::NETHER_GOLD_ORE()->getTypeId() or $block->getTypeId() != VanillaBlocks::NETHER_QUARTZ_ORE()->getTypeId()) {

                $event->cancel();
            }
        }

        if ($world === Server::getInstance()->getWorldManager()->getWorldByName("farmzone")) {

            if ($block->getTypeId() == VanillaBlocks::GOLD_ORE()->getTypeId() or $block->getTypeId() == VanillaBlocks::DIAMOND_ORE()->getTypeId() or $block->getTypeId() == VanillaBlocks::EMERALD_ORE()->getTypeId() or $block->getTypeId() == VanillaBlocks::NETHER_GOLD_ORE()->getTypeId() or $block->getTypeId() == VanillaBlocks::NETHER_QUARTZ_ORE()->getTypeId()) {

                //$event->cancel();

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

        if (!$this->canAccess($event->getPlayer()) and $this->isInArea($event->getPlayer()->getPosition()->asVector3())) {
            $event->getPlayer()->sendActionBarMessage('§cVous ne pouvez pas accéder à cette mine.');
            $event->cancel();
        }
    }

    public function canAccess(Player $player): bool
    {
        return Linesia::getInstance()->getData()->getNested("{$player->getName()}.quest", 1) === 6;
    }

    public function isInArea(Vector3 $pos): bool
    {
        $minX = min(180);
        $maxX = max(58);
        $minY = min(100);
        $maxY = max(0);
        $minZ = min(170);
        $maxZ = max(300);
        return ($pos->x >= $minX && $pos->x <= $maxX) &&
            ($pos->y >= $minY && $pos->y <= $maxY) &&
            ($pos->z >= $minZ && $pos->z <= $maxZ);
    }

}