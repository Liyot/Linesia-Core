<?php

namespace refaltor\inventoryapi\events;

use pocketmine\event\inventory\InventoryTransactionEvent;
use pocketmine\event\Listener;
use pocketmine\inventory\transaction\action\SlotChangeAction;
use refaltor\inventoryapi\inventories\SimpleChestInventory;

class PacketListener implements Listener
{
    public function onInventoryTransaction(InventoryTransactionEvent $event) : void {
        $transaction = $event->getTransaction();
        $player = $transaction->getSource();
        foreach ($transaction->getActions() as $action) {
            if ($action instanceof SlotChangeAction) {
                $inventory = $action->getInventory();
                if ($inventory instanceof SimpleChestInventory) {
                    $clickCallback = $inventory->getClickListener();
                    if ($clickCallback !== null) {
                        $clickCallback($player, $inventory, $action->getSourceItem(), $action->getTargetItem(), $action->getSlot());
                    }
                    if ($inventory->isCancelTransaction()) {
                        $event->cancel();
                        $inventory->reloadTransaction();
                    }
                    if ($inventory->isViewOnly()) {
                        $event->cancel();
                    }
                }
            }
        }
    }
}