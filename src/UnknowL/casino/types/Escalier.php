<?php

namespace UnknowL\casino\types;

use pocketmine\block\Concrete;
use pocketmine\block\utils\DyeColor;
use pocketmine\block\VanillaBlocks;
use pocketmine\item\Item;
use pocketmine\item\VanillaItems;
use UnknowL\lib\inventoryapi\inventories\BaseInventoryCustom;
use UnknowL\lib\inventoryapi\inventories\DoubleInventory;
use UnknowL\lib\inventoryapi\inventories\SimpleChestInventory;
use UnknowL\player\LinesiaPlayer;
use UnknowL\trait\InventoryContainerTrait;

final class Escalier extends CasinoGame
{
	use InventoryContainerTrait;

	/**
	 * @var array{ string: int } $lines
	 */
	private array $lines = [];

	public function getName(): string
    {
		return "Escalier";
    }

    public function getDescription(): string
    {
		return "Choissisez une case parmis les 4 et tentez de doublez votre mise";
	}

    public function start(LinesiaPlayer $player, int $mise): void
    {
		$this->mise = $mise;
		$inventory = $this->getInventory();
		$this->lines[$player->getUniqueId()->toString()] = 0;
		$loose = false;

		/**@param DoubleInventory $inventory*/
		$inventory->setClickListener(function (LinesiaPlayer $player, BaseInventoryCustom $inventory, Item $sourceItem, Item $targetItem, int $slot) {
			if($sourceItem->getTypeId() === VanillaBlocks::CONCRETE()->setColor(DyeColor::BLACK())->asItem()->getTypeId())
			{
				$rand = random_int(0, 3);
				$line = $this->getInventoryLine($slot, $inventory);
				if($this->lines[$player->getUniqueId()->toString()] === $line[0])
				{
					if(empty(array_filter($line[1],  fn(Item $value) => $value->getCustomName() === "Au suivant !")))
					{
						if($sourceItem->getNamedTag()->getInt("InvCount") === $rand)
						{
							$inventory->setItem($slot, VanillaBlocks::TNT()->asItem()->setCustomName("Perdu !"));
							$this->loose($player);
							$inventory->setClickListener(null);
							return;
						}
                        $inventory->setItem(53, VanillaItems::DYE()
                            ->setColor(DyeColor::GREEN())
                            ->setCustomName(sprintf("Récupérez vos gains (%d)", round(count($this->getItems(VanillaItems::EMERALD(), $inventory))  * $this->mise * 1.20))));
						$inventory->setItem($slot, VanillaItems::EMERALD()->setCustomName("Au suivant !"));
						$this->nextLine($player, $inventory);
					}
				}
			}

			if ($sourceItem->getTypeId() === VanillaItems::DYE()->setColor(DyeColor::GREEN())->getTypeId())
			{
				$inventory->onClose($player);
				$gain = round(count($this->getItems(VanillaItems::EMERALD(), $inventory))  * $this->mise * 1.20);
				$this->win($player, $gain);
			}
		});
		$inventory->send($player);
    }

	/**
	 * @param LinesiaPlayer $player
	 * @param SimpleChestInventory $inventory
	 * @return void
	 */
	protected function nextLine(LinesiaPlayer $player, BaseInventoryCustom $inventory): void
	{
		if(!($this->lines[$player->getUniqueId()->toString()] === 6))
		{
			$this->lines[$player->getUniqueId()->toString()] += 1;
			return;
		}
		$inventory->onClose($player);
		$gain = count($this->getItems(VanillaBlocks::EMERALD()->asItem(), $inventory)) * $this->mise * 1.20;
		$this->win($player, $gain);
	}

	public function getContents(): array
	{
		$content = [];
		$count = 0;
		$bypass = 0;
		for ($i = 0; $i < 53; $i++)
		{
			match (true)
			{
				$i === 0 || $i % 9 === 0 || $bypass > 0 => array_push( $content,
					VanillaBlocks::CONCRETE()->setColor(DyeColor::BLACK())->asItem()->setNamedTag(VanillaBlocks::CONCRETE()->asItem()->getNamedTag()->setInt("InvCount", $count++))),
				default => array_push($content, VanillaBlocks::STAINED_GLASS_PANE()->setColor(DyeColor::GRAY())->asItem())
			};
			$i % 9 !== 0 ?: $bypass = 4;
			$bypass <= 0 ?: $bypass--;
			$count !== 4 ?: $count = 0;
		}
		$content[53] = VanillaItems::DYE()->setColor(DyeColor::GREEN())->setCustomName("Récupérez vos gains");
		return $content;
	}

	public function win(LinesiaPlayer $player, int $gain): void
	{
		$player->sendMessage("Vous avez gagné");
		$player->getEconomyManager()->add($gain);
	}

	public function loose(LinesiaPlayer $player): void
	{
		$player->sendMessage("Vous avez perdu");
	}
}