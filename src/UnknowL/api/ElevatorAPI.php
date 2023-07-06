<?php

namespace UnknowL\api;

use pocketmine\block\Block;
use pocketmine\block\VanillaBlocks;
use pocketmine\world\World;

class ElevatorAPI {

    /**
     * @param int $x
     * @param int $y
     * @param int $z
     * @param World $level
     * @return Block|null
     */
    public static function isElevatorBlock(int $x, int $y, int $z, World $level): ?Block {
        $elevator = $level->getBlockAt($x, $y, $z);

        if ($elevator->getTypeId() !== VanillaBlocks::END_STONE()->getTypeId()) {
            return null;
        }

        return $elevator;
    }
}