<?php

namespace UnknowL\handlers\dataTypes;

use pocketmine\item\enchantment\EnchantmentInstance;
use pocketmine\item\Item;
use UnknowL\player\LinesiaPlayer;

class MarketData
{
	public function __construct(private Item $item, private EnchantmentInstance $enchantment, private int $price = 0,
								private string $description = "", private string $image = "", private string $name = "", private int $quantities = 64)
	{

	}

	final public function buy(LinesiaPlayer $player, MarketData $data, int $quantities): void
	{
		$player->getEconomyManager()->reduce($data->getPrice() * $quantities);
		$player->getInventory()->addItem($data->getItem()->setCount($quantities));
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