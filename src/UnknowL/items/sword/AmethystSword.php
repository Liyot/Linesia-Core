<?php

namespace UnknowL\items\sword;

use pocketmine\item\ItemIdentifier;
use pocketmine\item\ItemTypeIds;
use pocketmine\item\Sword;
use pocketmine\item\ToolTier;
use UnknowL\lib\customies\item\component\DamageComponent;
use UnknowL\lib\customies\item\component\MaxStackSizeComponent;
use UnknowL\lib\customies\item\CreativeInventoryInfo;
use UnknowL\lib\customies\item\ItemComponents;
use UnknowL\lib\customies\item\ItemComponentsTrait;

class AmethystSword extends Sword implements ItemComponents
{
	use ItemComponentsTrait;

	public function __construct(ItemIdentifier $identifier, string $name = "Amethyst")
	{
		$identifier = new ItemIdentifier(ItemTypeIds::newId());
		parent::__construct($identifier, "Epée en Améthyste", ToolTier::DIAMOND());

		$creativeInfo = new CreativeInventoryInfo(CreativeInventoryInfo::CATEGORY_ITEMS, CreativeInventoryInfo::GROUP_SWORD);
		$this->initComponent("amethyste_sword", $creativeInfo);
		$this->addComponent(new MaxStackSizeComponent(1));
		$this->addComponent(new DamageComponent(4));
		$this->setupRenderOffsets(32, 32, true);
	}
}