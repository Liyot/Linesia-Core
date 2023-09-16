<?php

namespace UnknowL\trait;

use http\Exception\InvalidArgumentException;
use pocketmine\item\Item;
use pocketmine\item\ItemBlock;
use UnknowL\lib\inventoryapi\inventories\BaseInventoryCustom;
use UnknowL\lib\inventoryapi\inventories\SimpleChestInventory;
use UnknowL\lib\inventoryapi\InventoryAPI;

trait InventoryContainerTrait
{
	/**
	 * @var Item[]|ItemBlock[]
	 */
	private array $content = [];

	public function __construct()
	{
		$contents = $this->getContents();
		array_walk($contents, fn(ItemBlock|Item $item, int $key) => $this->setObject($item, $key));
	}

	public function getInventoryLine(int $slot, BaseInventoryCustom $inventory): ?array
	{
		$i = 5;
		while ($i >= 1)
		{
			if ($slot >= $i * 9 && $slot <= $i * 9 + 9)
			{
				return [abs(5 - $i), array_map(fn($value) => $inventory->getItem($value), range($i * 9, ($i * 9 + 9) - 1))];
			}
			$i--;
		}
		return [5, array_map(fn($value) => $inventory->getItem($value), range(0, 8))];
	}

	final public function getItems(Item $search, SimpleChestInventory $inventory): ?array
	{
		return array_filter($inventory->getContents(), fn(Item $item) => $item->getTypeId() === $search->getTypeId());
	}

    /**
     * @param int $start
     * @param int $stop
     * @return Item[]|ItemBlock[]
     */
    final public function getInterval(int $start, int $stop) : array
    {
        $items = [];
        for ($i = $start; $start <= $stop ; $i++)
        {
            $items[] = $this->getObject($i);
        }
        return $items;
    }

	public function getObject(int $pos): Item|ItemBlock
	{
		return $this->content[$pos] ?? throw new InvalidArgumentException("Unvalid array pos $pos");
	}

	public function setObject(Item|ItemBlock $block, int $pos): void
	{
		$this->content[$pos] = $block;
	}

	final public function getInventory(): SimpleChestInventory
	{
		$invetory = InventoryAPI::createDoubleChest(true);
		$invetory->setContents($this->content);
		return $invetory;
	}

	abstract public function getContents(): array;
}