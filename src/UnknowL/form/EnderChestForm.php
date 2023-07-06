<?php

namespace UnknowL\form;

use muqsit\invmenu\InvMenu;
use muqsit\invmenu\transaction\InvMenuTransaction;
use muqsit\invmenu\transaction\InvMenuTransactionResult;
use muqsit\invmenu\type\InvMenuTypeIds;
use pocketmine\network\mcpe\protocol\PlaySoundPacket;
use pocketmine\player\Player;

class EnderChestForm {

    protected Player $sender;

    public function __construct(Player $sender) {
        $this->sender = $sender;
    }

    public function open(): void {
        $menu = InvMenu::create(InvMenuTypeIds::TYPE_CHEST);
        $menu->setName("§d- §fEnderChest §d-");
        $menu->getInventory()->setContents($this->sender->getEnderInventory()->getContents());
        $menu->setListener(function(InvMenuTransaction $transaction): InvMenuTransactionResult {
           $this->sender->getEnderInventory()->setItem($transaction->getAction()->getSlot(), $transaction->getIn());
           return $transaction->continue();
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