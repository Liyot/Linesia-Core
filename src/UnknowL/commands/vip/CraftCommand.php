<?php

namespace UnknowL\commands\vip;

use pocketmine\block\inventory\CraftingTableInventory;
use pocketmine\block\tile\Nameable;
use pocketmine\block\VanillaBlocks;
use pocketmine\command\CommandSender;
use pocketmine\nbt\tag\CompoundTag;
use pocketmine\network\mcpe\convert\TypeConverter;
use pocketmine\network\mcpe\protocol\BlockActorDataPacket;
use pocketmine\network\mcpe\protocol\types\BlockPosition;
use pocketmine\network\mcpe\protocol\types\CacheableNbt;
use pocketmine\network\mcpe\protocol\UpdateBlockPacket;
use pocketmine\player\Player;
use pocketmine\Server;
use pocketmine\world\Position;
use UnknowL\lib\commando\BaseCommand;
use UnknowL\lib\commando\constraint\InGameRequiredConstraint;
use UnknowL\Linesia;
use UnknowL\player\LinesiaPlayer;

class CraftCommand extends BaseCommand
{

    public function __construct()
    {
        $settings = Linesia::getInstance()->getCommandManager()->getSettings("craft");
        parent::__construct(Linesia::getInstance(), $settings->getName(), $settings->getDescription(), $settings->getAliases());
    }

    protected function prepare(): void
    {
        $this->setPermission("craft.use");
        $this->addConstraint(new InGameRequiredConstraint($this));
    }

    /**
     * @param LinesiaPlayer $sender
     */
    public function onRun(CommandSender $sender, string $aliasUsed, array $args): void
    {
		$holder = new Position((int)$sender->getPosition()->getX(), (int)$sender->getPosition()->getY() + 3, (int)$sender->getPosition()->getZ(), $sender->getWorld());
		$sender->getNetworkSession()->sendDataPacket(UpdateBlockPacket::create(BlockPosition::fromVector3($holder), TypeConverter::getInstance()->getBlockTranslator()->internalIdToNetworkId(VanillaBlocks::CRAFTING_TABLE()->getStateId()), UpdateBlockPacket::FLAG_NETWORK, UpdateBlockPacket::DATA_LAYER_NORMAL));
		$nbt = CompoundTag::create()->setString(Nameable::TAG_CUSTOM_NAME, $this->getName());
		$packet = BlockActorDataPacket::create(BlockPosition::fromVector3($holder), new CacheableNbt($nbt));
		$sender->getNetworkSession()->sendDataPacket($packet);
		$sender->setCurrentWindow(new CraftingTableInventory($holder));
		$sender->setInCrafting(true);
    }
}