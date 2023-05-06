<?php

namespace UnknowL\lib\inventoryapi;

use pocketmine\inventory\Inventory;
use pocketmine\item\Item;
use pocketmine\item\VanillaItems;
use pocketmine\player\Player;
use pocketmine\plugin\PluginBase;
use pocketmine\Server;
use pocketmine\utils\SingletonTrait;
use UnknowL\lib\inventoryapi\inventories\{BaseInventoryCustom, SimpleChestInventory, DoubleInventory};

class InventoryAPI 
{

    public static function createSimpleChest(bool $isViewOnly = false): SimpleChestInventory {
        $inventory = new SimpleChestInventory();
        $inventory->setViewOnly($isViewOnly);
        return $inventory;
    }

    public static function createDoubleChest(bool $isViewOnly = false): SimpleChestInventory {
        $inventory = new DoubleInventory();
        $inventory->setViewOnly($isViewOnly);
        return $inventory;
    }

    public function getDelaySend(): int {
        return 10;
    }
}
