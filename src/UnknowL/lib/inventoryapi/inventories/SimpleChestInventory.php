<?php

namespace UnknowL\lib\inventoryapi\inventories;

use pocketmine\block\VanillaBlocks;
use pocketmine\nbt\tag\CompoundTag;
use pocketmine\network\mcpe\convert\TypeConverter;
use pocketmine\network\mcpe\protocol\BlockActorDataPacket;
use pocketmine\network\mcpe\protocol\UpdateBlockPacket;
use pocketmine\network\mcpe\protocol\types\BlockPosition;
use pocketmine\network\mcpe\protocol\types\CacheableNbt;
use pocketmine\player\Player;
use pocketmine\block\tile\Nameable;
use pocketmine\scheduler\ClosureTask;
use pocketmine\world\BlockTransaction;
use pocketmine\world\Position;
use UnknowL\Linesia;
use UnknowL\player\LinesiaPlayer;

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

    public function send(LinesiaPlayer $player)
    {
        if (!isset($this->hasSend[$player->getXuid()]))
		{
			$this->holder = new Position((int)$player->getPosition()->getX(), (int)$player->getPosition()->getY() + 3, (int)$player->getPosition()->getZ(), $player->getWorld());
			$player->getNetworkSession()->sendDataPacket(UpdateBlockPacket::create(BlockPosition::fromVector3($this->holder), TypeConverter::getInstance()->getBlockTranslator()->internalIdToNetworkId(VanillaBlocks::CHEST()->getStateId()), UpdateBlockPacket::FLAG_NETWORK, UpdateBlockPacket::DATA_LAYER_NORMAL));
			$nbt = CompoundTag::create()->setString(Nameable::TAG_CUSTOM_NAME, $this->getName());
			$packet = BlockActorDataPacket::create(BlockPosition::fromVector3($this->holder), new CacheableNbt($nbt));
			$player->getNetworkSession()->sendDataPacket($packet);
			$player->setCurrentWindow($this);
			$this->hasSend[$player->getXuid()] = true;
        }
    }
}
