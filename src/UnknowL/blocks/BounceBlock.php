<?php

namespace UnknowL\events;

use pocketmine\block\VanillaBlocks;
use pocketmine\event\Listener;
use pocketmine\event\player\PlayerMoveEvent;
use pocketmine\math\Vector3;

class BounceBlock implements Listener {

    public function blockBounce(PlayerMoveEvent $event) {
        $sender = $event->getPlayer();
        $world = $sender->getLocation()->getWorld();
        $position = $sender->getPosition();

        $under = $world->getBlock($position->add(0, 0, 0));
        if($under->getTypeId() == VanillaBlocks::PRISMARINE() && $under->getStateId() == VanillaBlocks::PRISMARINE()->getStateId()) {
            $sender->setMotion(new Vector3(0, 1, 1.5));
        }
    }

}