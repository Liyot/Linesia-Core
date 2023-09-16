<?php

namespace UnknowL\entities\monsters;

use pocketmine\entity\Human;
use pocketmine\entity\Location;
use pocketmine\entity\Skin;
use pocketmine\math\AxisAlignedBB;
use pocketmine\nbt\tag\CompoundTag;
use pocketmine\Server;
use UnknowL\player\LinesiaPlayer;

class BaseMonster extends Human
{

    private AxisAlignedBB $zone;

    public function __construct(Location $location, Skin $skin, ?CompoundTag $nbt = null)
    {
       // $this->zone = new AxisAlignedBB();
        parent::__construct($location, $skin, $nbt);
    }

    public function onUpdate(int $currentTick): bool
    {

        return parent::onUpdate($currentTick);
    }

    protected function getNearestPlayer(LinesiaPlayer $player)
    {
        foreach ($this->)
    }
}