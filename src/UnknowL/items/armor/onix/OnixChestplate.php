<?php

namespace UnknowL\items\armor\onix;

use pocketmine\inventory\ArmorInventory;
use pocketmine\item\Armor;
use pocketmine\item\ArmorTypeInfo;
use pocketmine\item\ItemIdentifier;
use pocketmine\item\ItemTypeIds;
use UnknowL\lib\customies\item\component\MaxStackSizeComponent;
use UnknowL\lib\customies\item\CreativeInventoryInfo;
use UnknowL\lib\customies\item\ItemComponents;
use UnknowL\lib\customies\item\ItemComponentsTrait;

class OnixChestplate extends Armor implements ItemComponents
{
	use ItemComponentsTrait;

	public function __construct(ItemIdentifier $identifier, string $name = "Plastron en Améthyste")
	{
		$identifier = new ItemIdentifier(ItemTypeIds::newId());
		parent::__construct($identifier, "Plastron en Onix", new ArmorTypeInfo(3, 1750, ArmorInventory::SLOT_CHEST));
		$creativeInfo = new CreativeInventoryInfo(CreativeInventoryInfo::CATEGORY_ITEMS, CreativeInventoryInfo::GROUP_HELMET);
		$this->initComponent("onix_chestplate", $creativeInfo);
		$this->addComponent(new MaxStackSizeComponent(1));
		$this->setupRenderOffsets(64, 64, true);
	}
}