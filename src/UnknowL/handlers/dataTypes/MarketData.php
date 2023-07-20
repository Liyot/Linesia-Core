<?php

namespace UnknowL\handlers\dataTypes;

use pocketmine\data\bedrock\item\SavedItemStackData;
use pocketmine\item\enchantment\EnchantmentInstance;
use pocketmine\item\Item;
use UnknowL\player\LinesiaPlayer;

class MarketData
{
	public function __construct
	(
		private Item $item,
		private int|float $sellPrice = 0,
		private string $description = "",
		private string $image = "",
		private string $name = "",
		private int $quantities = 64,
		private int|float $buyPrice = 0,
	){}

	final public function buy(LinesiaPlayer $player, int $quantities): void
	{
		if ($player->getEconomyManager()->reduce(round($this->getBuyPrice() * $quantities, 1)))
		{
			$player->getInventory()->addItem($this->getItem()->setCount($quantities));
		}
	}

    final public function sell(LinesiaPlayer $player, int $quantities): void
    {
		if ($player->getInventory()->contains($this->item->setCount($quantities)))
		{
			$player->getEconomyManager()->add(round($quantities * $this->getSellPrice(), 1));
			$player->getInventory()->remove($this->getItem()->setCount($quantities));
			$player->sendMessage("§aVous avez vendu votre item avec succés !");
			return;
		}
		$player->sendMessage("§cVous n'avez pas assez d'item dans votre inventaire !");
    }

	/**
	 * @return Item
	 */
	public function getItem(): Item
	{
		return $this->item;
	}


	/**
	 * @return int
	 */
	public function getSellPrice(): int|float
	{
		return $this->sellPrice;
	}

	public function getBuyPrice(): int|float
	{
		return $this->buyPrice;
	}

	/**
	 * @return string
	 */
	public function getDescription(): string
	{
		return $this->description;
	}

	/**
	 * @return string
	 */
	public function getImage(): string
	{
		return $this->image;
	}

	/**
	 * @return string
	 */
	public function getName(): string
	{
		return $this->name;
	}

	/**
	 * @return int
	 */
	public function getQuantities(): int
	{
		return $this->quantities;
	}
}