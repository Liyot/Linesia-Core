<?php

namespace UnknowL\lib\inventoryapi\inventories;

use pocketmine\block\VanillaBlocks;
use pocketmine\nbt\tag\CompoundTag;
use pocketmine\network\mcpe\convert\RuntimeBlockMapping;
use pocketmine\network\mcpe\protocol\BlockActorDataPacket;
use pocketmine\network\mcpe\protocol\UpdateBlockPacket;
use pocketmine\network\mcpe\protocol\types\BlockPosition;
use pocketmine\network\mcpe\protocol\types\CacheableNbt;
use pocketmine\player\Player;
use pocketmine\block\tile\Nameable;
use pocketmine\world\Position;

class SimpleChestInventory extends BaseInventoryCustom
{
    private array $hasSend = [];

    public function onClose(Player $who): void
    {
        if (isset($this->hasSend[$who->getXuid()])) {
            unset($this->hasSend[$who->getXuid()]);
        }
        parent::onClose($who);
    }

    public function send(Player $player)
    {
        if (!isset($this->hasSend[$player->getXuid()])) {
            $this->holder = new Position((int)$player->getPosition()->getX(), (int)$player->getPosition()->getY() + 3, (int)$player->getPosition()->getZ(), $player->getWorld());
            $player->getNetworkSession()->sendDataPacket(UpdateBlockPacket::create(BlockPosition::fromVector3($this->holder), RuntimeBlockMapping::getInstance()->toRuntimeId(VanillaBlocks::CHEST()->getFullId()), UpdateBlockPacket::FLAG_NETWORK, UpdateBlockPacket::DATA_LAYER_NORMAL));
            $nbt = CompoundTag::create()->setString(Nameable::TAG_CUSTOM_NAME, $this->getName());
            $packet = BlockActorDataPacket::create(BlockPosition::fromVector3($this->holder), new CacheableNbt($nbt));
            $player->getNetworkSession()->sendDataPacket($packet);
            $player->setCurrentWindow($this);
            $this->hasSend[$player->getXuid()] = true;
        }
    }
}
