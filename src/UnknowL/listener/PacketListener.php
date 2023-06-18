<?php

namespace UnknowL\listener;

use pocketmine\event\inventory\InventoryTransactionEvent;
use pocketmine\event\Listener;
use pocketmine\event\server\DataPacketReceiveEvent;
use pocketmine\inventory\PlayerOffHandInventory;
use pocketmine\inventory\transaction\action\SlotChangeAction;
use pocketmine\network\mcpe\protocol\PlayerActionPacket;
use pocketmine\network\mcpe\protocol\PlayerAuthInputPacket;
use pocketmine\network\mcpe\protocol\types\inventory\UseItemTransactionData;
use pocketmine\network\mcpe\protocol\types\PlayerAuthInputFlags;
use pocketmine\network\mcpe\protocol\types\PlayerBlockActionWithBlockInfo;
use UnknowL\lib\inventoryapi\inventories\SimpleChestInventory;

final class PacketListener implements Listener 
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

//	public function onReceive(DataPacketReceiveEvent $event)
//	{
//		$packet = $event->getPacket();
//		$player = $event->getOrigin()->getPlayer();
//		if($packet instanceof PlayerAuthInputPacket)
//		{
//			if (!is_null($packet->getBlockActions())) var_dump($packet);
//		}
//	}
}