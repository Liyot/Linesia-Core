<?php

namespace UnknowL\form;

use pocketmine\item\Item;
use pocketmine\network\mcpe\protocol\PlaySoundPacket;
use UnknowL\lib\inventoryapi\inventories\BaseInventoryCustom;
use UnknowL\lib\inventoryapi\InventoryAPI;
use UnknowL\player\LinesiaPlayer;

class EnderChestForm {

    public function __construct(protected LinesiaPlayer $sender) {
    }

    public function open(): void {
        $menu = $form = InventoryAPI::createSimpleChest();
        $menu->setName("§d- §fEnderChest §d-");
        $menu->setContents($this->sender->getEnderInventory()->getContents());
		$menu->setCloseListener(function(LinesiaPlayer $player, BaseInventoryCustom $inventory)
		{
			$player->getEnderInventory()->setContents($inventory->getContents());
		});
        $menu->send($this->sender);
        $this->sound();
    }

    private function sound(): void {
        $packet = new PlaySoundPacket();
        $packet->x = $this->sender->getPosition()->x;
        $packet->y = $this->sender->getPosition()->y;
        $packet->z = $this->sender->getPosition()->z;
        $packet->soundName = "random.enderchestopen";
        $packet->volume = 1;
        $packet->pitch = 1;
        $this->sender->getNetworkSession()->sendDataPacket($packet);
    }
}