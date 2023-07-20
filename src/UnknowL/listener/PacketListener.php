<?php

namespace UnknowL\listener;

use DaPigGuy\PiggyFactions\permissions\FactionPermission;
use DaPigGuy\PiggyFactions\PiggyFactions;
use pocketmine\event\inventory\InventoryTransactionEvent;
use pocketmine\event\Listener;
use pocketmine\event\server\DataPacketDecodeEvent;
use pocketmine\event\server\DataPacketReceiveEvent;
use pocketmine\event\server\DataPacketSendEvent;
use pocketmine\inventory\transaction\action\SlotChangeAction;
use pocketmine\network\mcpe\cache\CraftingDataCache;
use pocketmine\network\mcpe\convert\TypeConverter;
use pocketmine\network\mcpe\NetworkSession;
use pocketmine\network\mcpe\protocol\CraftingDataPacket;
use pocketmine\network\mcpe\protocol\CraftingEventPacket;
use pocketmine\network\mcpe\protocol\DataPacket;
use pocketmine\network\mcpe\protocol\InventoryTransactionPacket;
use pocketmine\network\mcpe\protocol\PlayerActionPacket;
use pocketmine\network\mcpe\protocol\PlayerAuthInputPacket;
use pocketmine\network\mcpe\protocol\types\inventory\UseItemTransactionData;
use pocketmine\network\mcpe\protocol\types\PlayerAction;
use pocketmine\network\mcpe\protocol\types\recipe\ShapelessRecipe;
use pocketmine\network\mcpe\protocol\UpdateBlockPacket;
use pocketmine\player\Player;
use pocketmine\Server;
use pocketmine\world\Position;
use UnknowL\lib\inventoryapi\inventories\SimpleChestInventory;
use UnknowL\Linesia;

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

	public function onEvent(DataPacketDecodeEvent $event): void
	{
		if (strlen($event->getPacketBuffer()) > 8096 and $event->getPacketId() !== 1) {
			Linesia::getInstance()->getLogger()->warning("Undecoded PacketID: {$event->getPacketId()} (size: " . strlen($event->getPacketBuffer()) . ") from IP: {$event->getOrigin()->getIp()}");
			$event->getOrigin()->disconnect("Une erreur est survenue lors de l'encodage du paquet");
			$event->cancel();
		}
	}

	public function onReceive(DataPacketReceiveEvent $event)
	{
		$packet = $event->getPacket();
		$player = $event->getOrigin()->getPlayer();
		if (is_null($player)) return;

		if ($packet instanceof InventoryTransactionPacket)
		{
			$data = $packet->trData;
			if ($data instanceof UseItemTransactionData)
			{
				$blockPosition = $data->getBlockPosition();
				if (!$this->canAffectArea($player, new Position($blockPosition->getX(), $blockPosition->getY(), $blockPosition->getZ(), $player->getWorld())))
				{
					$player->getNetworkSession()->sendDataPacket(UpdateBlockPacket::create($blockPosition, $data->getBlockRuntimeId(), UpdateBlockPacket::FLAG_NETWORK,UpdateBlockPacket::DATA_LAYER_NORMAL));
					$event->cancel();
				}
			}
		}
	}

	public function canAffectArea(Player $player, Position $position, string $type = FactionPermission::BUILD): bool
	{
		$member = PiggyFactions::getInstance()->getPlayerManager()->getPlayer($player);
		$claim = PiggyFactions::getInstance()->getClaimsManager()->getClaimByPosition($position);
		if ($claim !== null) return $member !== null && $claim->getFaction()->hasPermission($member, $type);
		return true;
	}

}