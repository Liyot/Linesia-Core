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
		private EnchantmentInstance $enchantment,
		private int $price = 0,
		private string $description = "",
		private string $image = "",
		private string $name = "",
		private int $quantities = 64
	){}

	final public function buy(LinesiaPlayer $player, int $quantities): void
	{
		$player->getEconomyManager()->reduce($this->getPrice() * $quantities);
		$player->getInventory()->addItem($this->getItem()->setCount($quantities));
	}

    final public function sell(LinesiaPlayer $player, int $quantities)
    {
        $player->getEconomyManager()->add($quantities * $this->getPrice());
        $player->getInventory()->remove($this->getItem()->setCount($quantities));
        $player->sendMessage("Vous avez vendu votre item avec succés");
    }

	/**
	 * @return Item
	 */
	public function getItem(): Item
	{
		return $this->item;
	}

	/**
	 * @return EnchantmentInstance
	 */
	public function getEnchantment(): EnchantmentInstance
	{
		return $this->enchantment;
	}

	/**
	 * @return int
	 */
	public function getPrice(): int
	{
		return $this->price;
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