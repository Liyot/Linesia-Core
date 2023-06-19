<?php

namespace UnknowL\casino\types;

use pocketmine\block\VanillaBlocks;
use pocketmine\item\Item;
use pocketmine\item\ItemTypeIds;
use pocketmine\item\VanillaItems;
use pocketmine\scheduler\Task;
use UnknowL\lib\inventoryapi\inventories\BaseInventoryCustom;
use UnknowL\lib\inventoryapi\inventories\SimpleChestInventory;
use UnknowL\lib\inventoryapi\InventoryAPI;
use UnknowL\Linesia;
use UnknowL\player\LinesiaPlayer;
use UnknowL\trait\InventoryContainerTrait;

//Inner task may be more appropriate fort this type of game

class Pyramides extends CasinoGame
{
	use InventoryContainerTrait;

    public function getName(): string
    {
		return "Pyramides";
	}

    public function getDescription(): string
    {
		return "Choississez une mise et multiplier la par la somme du hopper dans lequel elle tombe";
	}

    public function start(LinesiaPlayer $player, int $mise): void
    {
		$this->mise = $mise;
		Linesia::getInstance()->getScheduler()->scheduleRepeatingTask
		(
			new class($player, $this) extends Task
			{
				private int $step = 0;

				private int $slot = 0;

				private SimpleChestInventory $inventory;

				public function __construct(private LinesiaPlayer $player, private Pyramides $game)
				{
					$inventory = $this->game->getInventory();
					$inventory->setClickListener(function (LinesiaPlayer $player, BaseInventoryCustom $inventory, Item $sourceItem, Item $targetItem, int $slot)
					{
						if($sourceItem->getTypeId() === VanillaBlocks::EMERALD()->asItem()->getTypeId())
						{
							$this->step = 1;
							$inventory->setItem(4, VanillaItems::SLIMEBALL()->setCustomName("Bille"));
							$this->slot = 4;
						}
					});
					$this->inventory = $inventory;
					$inventory->send($player);
				}

				public function onRun(): void
				{
					$bille = $this->inventory->getItem($this->slot);
					if($this->step > 0 && $this->step <= 3)
					{
						$rand = rand(1, 2);
						$this->inventory->removeItem($bille);
						$slot = $this->getAvailableSlot();
						match ($slot[0])
						{
							0, 1 => $this->inventory->setItem($slot[1], $bille),
							default => $this->inventory->setItem($slot[$rand], $bille)
						};
						$this->slot = count($slot) === 2 ? $slot[1] : $slot[$rand];
						$this->step++;
						return;
					}

					if ($this->step === 4)
					{
						$this->inventory->removeItem($bille);
						$newSlot = match (random_int(0, 2))
						{
							0 => $this->slot + 9,
							1 => $this->slot + 8,
							default => $this->slot + 10
						};
						$this->inventory->setItem($newSlot, $bille);
						$this->slot += 9;
						$this->step++;
						return;
					}
					if ($this->step === 5)
					{
						$multiplier = explode(":", $this->inventory->getItem($this->slot + 9)->getCustomName())[1];
						$gain = substr($multiplier, 0, stripos($multiplier, "x"));
						var_dump($gain);
						$this->game->win($this->player, (float)$gain);
						$this->inventory->onClose($this->player);
						$this->getHandler()->cancel();
					}
				}

				/**
				 * @return array
				 */
				public function getAvailableSlot(): array
				{
					$inventory = $this->inventory;
					$slot = $this->slot + 9;
					$line = $this->game->getInventoryLine($slot, $inventory);
					return match (true)
					{
						in_array($inventory->getItem($slot + 1), $line, true) && !in_array($inventory->getItem($slot - 1), $line, true) => [0, $slot + 1],
						in_array($inventory->getItem($slot - 1), $line, true) && !in_array($inventory->getItem($slot + 1), $line, true) => [1, $slot - 1],
						default => [2, $slot + 1, $slot - 1]
					};
				}
			}
		, 20);
	}

    public function win(LinesiaPlayer $player, float|int $gain): void
    {
		var_dump($this->mise, $gain);
		$player->getEconomyManager()->add($this->mise * $gain);
    }

    public function loose(LinesiaPlayer $player): void
    {
		//Not loosable game
    }

	public function getContents(): array
	{
		$content = [];
		$multiplier = array_merge(($array = ['5', '3', '1.20', '0.50', '0.20']), array_slice(array_reverse($array), 1));
		for ($slot = 0; $slot < 54 ; $slot++)
		{
			if($slot < 9) continue;
			if($slot >= 45)
			{
				//To get the multiplier without skipping first value
				$combo = $slot === 45 ? current($multiplier) : next($multiplier);
				$content[$slot] = VanillaBlocks::HOPPER()->asItem()->setCustomName("Multiplicateur de mise: $combo x");
				continue;
			}
			if ($slot % 2 !== 0 && $slot <= 35)
			{
				$content[$slot] = VanillaBlocks::PACKED_ICE()->asItem()->setCustomName("ice");
			}
		}

		$content[4] = VanillaBlocks::EMERALD()->asItem()->setCustomName("Lancer la bille");

		return $content;
	}
}