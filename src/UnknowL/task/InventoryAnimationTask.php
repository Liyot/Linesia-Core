<?php

namespace UnknowL\task;

use pocketmine\block\VanillaBlocks;
use pocketmine\item\Item;
use pocketmine\item\ItemBlock;
use pocketmine\scheduler\Task;
use UnknowL\lib\inventoryapi\inventories\SimpleChestInventory;
use UnknowL\player\LinesiaPlayer;

class InventoryAnimationTask extends Task
{

	private int $cooldown = 0, $times = 0;


	public function __construct(private array $items, private SimpleChestInventory $inventory, protected LinesiaPlayer $player)
	{
		$this->randInventory();
	}

	public function onRun(): void
	{
		if ($this->cooldown <= 0)
		{
			foreach ($this->inventory->getContents() as $slot => $item)
			{
				if($slot >= 9 && $slot <= 17)
				{
					if ($slot === 9)
					{
						$this->inventory->removeItem($item);
					}

					if($slot === 17)
					{
						$this->inventory->setItem(17, $this->next());
					}
					$this->inventory->setItem(($slot - 1 === 8) ? 9 : $slot - 1, $item);
				}
			}

			if($this->times >= 37) $this->cooldown = 3;

			if($this->times >= 42)
			{
				$this->getHandler()->cancel();
			}
			$this->times++;
		}
		$this->cooldown--;
	}

	public function onCancel(): void
	{
		if ($this->player->isConnected()) {
			$this->inventory->onClose($this->player);
		}
		parent::onCancel();
	}

	final public function getResult(): Item
	{
		return $this->inventory->getItem(14);
	}

	private function randInventory(): void
	{
		$array = range(0, 26);
		array_walk($array, fn($value, $key) => $this->inventory->setItem($key,VanillaBlocks::GLASS_PANE()->asItem()));
		for ($i = random_int(9, 37) ; $i > 0 ; $i--)
		{
			$this->next();
		}
		for ($i = 9 ; $i < 18 ; $i++)
		{
			$this->inventory->setItem($i, $this->next());
		}
	}

	private function next() : Item
	{
		$return = next($this->items);
		if(!$return)
		{
			reset($this->items);
			return current($this->items);
		}
		return $return;
	}
}