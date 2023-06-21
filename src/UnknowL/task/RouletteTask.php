<?php

namespace UnknowL\task;

use pocketmine\block\GlazedTerracotta;
use pocketmine\block\utils\DyeColor;
use pocketmine\block\VanillaBlocks;
use pocketmine\item\Item;
use pocketmine\item\ItemBlock;
use pocketmine\player\Player;
use pocketmine\scheduler\Task;
use UnknowL\lib\inventoryapi\inventories\BaseInventoryCustom;

final class RouletteTask extends Task
{

	public function __construct(private BaseInventoryCustom $inventory, private int $mise, private array $roulette, private Item $color, private Player $player, private int $cooldown = 0, private bool $first = true, private int $key = 0, private int $times = 0)
	{
		$array = range(0, 26);
		array_walk($array, fn($value, $key) => $inventory->setItem($key,VanillaBlocks::GLASS_PANE()->asItem()));
		for ($i = random_int(9, 37) ; $i > 0 ; $i--)
		{
			$this->next();
		}
		for ($i = 9 ; $i < 18 ; $i++)
		{
			$this->inventory->setItem($i, $this->next());
		}

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
		$final = $this->inventory->getItem(14);
		if ($this->player->isConnected())
		{
			if($final->getBlock()->getColor()->name() === $this->color->getBlock()->getColor()->name())
			{
				$gain = match ($final)
				{
					VanillaBlocks::CONCRETE()->setColor(DyeColor::GREEN())->asItem() => $this->mise * 14,
					default => $this->mise * 2
				};
				$this->player->sendMessage("Vous avez gagné ". $gain);
				$this->inventory->onClose($this->player);
				return;
			}
			$this->player->sendMessage("Vous n'avez rien gagner");
			$this->inventory->onClose($this->player);
		}
	}

	private function next() : ItemBlock
	{
		$return = next($this->roulette);
		if(!$return)
		{
			reset($this->roulette);
			return current($this->roulette);
		}
		return $return;
	}
}