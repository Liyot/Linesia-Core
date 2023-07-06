<?php

namespace UnknowL\api;

use pocketmine\entity\Entity;
use pocketmine\entity\EntitySizeInfo;
use pocketmine\network\mcpe\protocol\LevelSoundEventPacket;
use pocketmine\network\mcpe\protocol\PlaySoundPacket;
use pocketmine\network\mcpe\protocol\types\entity\EntityIds;
use pocketmine\network\mcpe\protocol\types\LevelSoundEvent;
use pocketmine\world\Position;

class LightningBolt extends Entity
{

    private int $age = 5;

    protected function getInitialSizeInfo(): EntitySizeInfo
    {
        return new EntitySizeInfo(1, 1);
    }

    public static function getNetworkTypeId(): string
    {
        return EntityIds::LIGHTNING_BOLT;
    }

    /**
     * @param int $tickDiff
     * @return bool
     */
    public function entityBaseTick(int $tickDiff = 1): bool
    {
        $parent = parent::entityBaseTick($tickDiff);

        //TODO Disgusting code, ill patch it later.
        if ($this->age === 1) {
            $this->sendImpactSound($this->getPosition());
            $this->sendRoarSound($this->getPosition());
        } else {
            $this->getWorld()->broadcastPacketToViewers($this->getPosition(), LevelSoundEventPacket::nonActorSound(LevelSoundEvent::THUNDER, $this->getPosition(), false));
        }
        --$this->age;
        if ($this->age < 1) {
            $this->flagForDespawn();
        }
        return $parent;
    }

    /**
     * @param Position $position
     * @param float $pitch
     * @param float $volume
     * @return void
     */
    public function sendImpactSound(Position $position, float $pitch = 1, float $volume = 0.5): void
    {
        $pk = PlaySoundPacket::create("ambient.weather.lightning.impact", $position->getX(), $position->getY(), $position->getZ(), $volume, $pitch);
        $this->getWorld()->broadcastPacketToViewers($position, $pk);
    }

    /**
     * @param Position $position
     * @param float $pitch
     * @param float $volume
     * @return void
     */
    public function sendRoarSound(Position $position, float $pitch = 1, float $volume = 0.1): void
    {
        foreach ($this->getWorld()->getPlayers() as $player) {
            $position = $player->getPosition();
            $pk = PlaySoundPacket::create("ambient.weather.thunder", $position->getX(), $position->getY(), $position->getZ(), $volume, $pitch);
            $player->getNetworkSession()->sendDataPacket($pk);
        }
    }

}