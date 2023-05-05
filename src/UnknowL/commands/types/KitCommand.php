<?php

namespace UnknowL\commands\types;

use pocketmine\command\CommandSender;
use UnknowL\lib\commando\constraint\InGameRequiredConstraint;
use UnknowL\lib\inventoryapi\inventories\SimpleChestInventory;
use UnknowL\lib\inventoryapi\InventoryAPI;
use UnknowL\player\LinesiaPlayer;

class KitCommand extends \UnknowL\lib\commando\BaseCommand
{

    /**
     * @inheritDoc
     */
    protected function prepare(): void
    {
		$this->addConstraint(new InGameRequiredConstraint($this));
	}

    /**
     * @inheritDoc
	 * @param LinesiaPlayer $sender
     */
    public function onRun(CommandSender $sender, string $aliasUsed, array $args): void
    {
        // TODO: Implement onRun() method.
    }

	public function getForm(): SimpleChestInventory
	{
		$form = InventoryAPI::createDoubleChest(true)->set
	}
}