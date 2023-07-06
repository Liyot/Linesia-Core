<?php

namespace UnknowL\task;

use pocketmine\player\Player;
use pocketmine\scheduler\Task;
use UnknowL\lib\inventoryapi\inventories\SimpleChestInventory;
use UnknowL\Linesia;

class DelayTask extends Task {

	public Player $player;
	public SimpleChestInventory $inventory;

	public function __construct(Player $player, SimpleChestInventory $inventory) {
		$this->player = $player;
		$this->inventory = $inventory;
	}

	public function onRun() : void {
		if ($this->player->isConnected()) {
			$this->player->setCurrentWindow($this->inventory);
		}
	}
}