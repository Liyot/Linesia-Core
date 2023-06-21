<?php

namespace UnknowL\events;

use pocketmine\event\Event;
use UnknowL\handlers\dataTypes\Cooldown;
use UnknowL\player\LinesiaPlayer;

class CooldownExpireEvent extends Event
{
    public function __construct(private Cooldown $cooldown, private ?LinesiaPlayer $player = null) {}

    /**
     * @return Cooldown
     */
    public function getCooldown(): Cooldown
    {
        return $this->cooldown;
    }

    /**
     * @return LinesiaPlayer|null
     */
    public function getPlayer(): ?LinesiaPlayer
    {
        return $this->player;
    }
}