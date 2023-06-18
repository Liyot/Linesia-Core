<?php

namespace UnknowL\lib\inventoryapi\inventories;

use pocketmine\block\VanillaBlocks;
use pocketmine\nbt\tag\CompoundTag;
use pocketmine\network\mcpe\convert\TypeConverter;
use pocketmine\scheduler\ClosureTask;
use UnknowL\task\DelayTask;
use pocketmine\network\mcpe\convert\RuntimeBlockMapping;
use pocketmine\network\mcpe\protocol\BlockActorDataPacket;
use pocketmine\network\mcpe\protocol\UpdateBlockPacket;
use pocketmine\network\mcpe\protocol\types\BlockPosition;
use pocketmine\network\mcpe\protocol\types\CacheableNbt;
use pocketmine\player\Player;
use pocketmine\block\tile\Nameable;
use pocketmine\world\Position;
use UnknowL\Linesia;

class DoubleInventory extends SimpleChestInventory {

    protected string $name = "Double chest";
    private array $hasSend = [];

    public function __construct()
    {
        parent::__construct(54);
    }

    public function onClose(Player $who): void
    {
        $who->getNetworkSession()->sendDataPacket(UpdateBlockPacket::create(BlockPosition::fromVector3($this->holder->add(1, 0, 0)), TypeConverter::getInstance()->getBlockTranslator()->internalIdToNetworkId($who->getWorld()->getBlock($this->holder)->getStateId()), UpdateBlockPacket::FLAG_NETWORK, UpdateBlockPacket::DATA_LAYER_NORMAL));
        if (isset($this->hasSend[$who->getXuid()])) {
            unset($this->hasSend[$who->getXuid()]);
        }
        parent::onClose($who);
    }

    public function send(Player $player)
    {
        if (!isset($this->hasSend[$player->getXuid()])) {
			Linesia::getInstance()->getScheduler()->scheduleDelayedTask(new ClosureTask(function () use ($player) {
				$this->holder = new Position((int)$player->getPosition()->getX(), (int)$player->getPosition()->getY() + 3, (int)$player->getPosition()->getZ(), $player->getWorld());
				$player->getNetworkSession()->sendDataPacket(UpdateBlockPacket::create(BlockPosition::fromVector3($this->holder), TypeConverter::getInstance()->getBlockTranslator()->internalIdToNetworkId(VanillaBlocks::CHEST()->getStateId()), UpdateBlockPacket::FLAG_NETWORK, UpdateBlockPacket::DATA_LAYER_NORMAL));
				$player->getNetworkSession()->sendDataPacket(UpdateBlockPacket::create(BlockPosition::fromVector3($this->holder->add(1, 0, 0)), TypeConverter::getInstance()->getBlockTranslator()->internalIdToNetworkId(VanillaBlocks::CHEST()->getStateId()), UpdateBlockPacket::FLAG_NETWORK, UpdateBlockPacket::DATA_LAYER_NORMAL));
				$nbt = CompoundTag::create()->setString(Nameable::TAG_CUSTOM_NAME, $this->getName())
					->setInt("pairx", $this->holder->x + 1)
					->setInt("pairz", $this->holder->z);
				$player->getNetworkSession()->sendDataPacket(BlockActorDataPacket::create(BlockPosition::fromVector3($this->holder), new CacheableNbt($nbt)));
				$player->getNetworkSession()->sendDataPacket(BlockActorDataPacket::create(BlockPosition::fromVector3($this->holder->add(1, 0, 0)), new CacheableNbt(CompoundTag::create())));
				Linesia::getInstance()->getScheduler()->scheduleDelayedTask(new DelayTask($player, $this), 20); // Delay for PS4 /!\ and switch GUI use bug.
				$this->hasSend[$player->getXuid()] = true;
			}), 20);
        }
    }
}
