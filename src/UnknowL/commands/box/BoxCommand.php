<?php

namespace UnknowL\commands\box;

use pocketmine\block\utils\DyeColor;
use pocketmine\block\VanillaBlocks;
use pocketmine\command\CommandSender;
use pocketmine\item\Item;
use pocketmine\item\VanillaItems;
use UnknowL\handlers\dataTypes\Box;
use UnknowL\handlers\Handler;
use UnknowL\lib\commando\args\RawStringArgument;
use UnknowL\lib\commando\BaseCommand;
use UnknowL\lib\commando\constraint\InGameRequiredConstraint;
use UnknowL\lib\inventoryapi\inventories\BaseInventoryCustom;
use UnknowL\lib\inventoryapi\InventoryAPI;
use UnknowL\Linesia;
use UnknowL\player\LinesiaPlayer;

class BoxCommand extends BaseCommand
{

	public function __construct()
	{
		$settings = Linesia::getInstance()->getCommandManager()->getSettings('box');
		parent::__construct(Linesia::getInstance(), $settings->getName(), $settings->getDescription(), $settings->getAliases());
	}

	/**
     * @inheritDoc
     */
    protected function prepare(): void
    {
		$this->registerArgument(0, new RawStringArgument("name"));
		$this->addConstraint(new InGameRequiredConstraint($this));
		$this->setPermission( "box.command.perm");
	}


	/**
     * @inheritDoc
	 * @param LinesiaPlayer $sender
     */
    public function onRun(CommandSender $sender, string $aliasUsed, array $args): void
    {
		if (isset($args["name"]) && is_string($args["name"]))
		{
			if (Handler::BOX()->getBox($args["name"]) !== null)
			{
				$sender->sendMessage("Cette boite est déjà enregistré");
				return;
			}

			$form = InventoryAPI::createDoubleChest(false);

			$modifiedItem = VanillaItems::AIR();
			$step = 0;
			$savedInventory = [];
			$list =
				[
					29 => VanillaBlocks::CONCRETE()->setColor(DyeColor::RED())->asItem()->setCustomName('- 10%'),
					30 => VanillaBlocks::CONCRETE()->setColor(DyeColor::PINK())->asItem()->setCustomName('- 1%'),
					31 => VanillaBlocks::CHEST()->asItem()->setCustomName("Validé"),
					32 => VanillaBlocks::CONCRETE()->setColor(DyeColor::YELLOW())->asItem()->setCustomName('+ 1%'),
					33 => VanillaBlocks::CONCRETE()->setColor(DyeColor::GREEN())->asItem()->setCustomName('+ 10%')
				];

			$percentage = 0;
			$form->setClickListener(function (LinesiaPlayer $player, BaseInventoryCustom $inventory, Item $sourceItem, Item $targetItem, int $slot) use (&$percentage, &$modifiedItem, &$savedInventory, $list, &$step)
			{
				if ($step === 1)
				{
					if ($slot === 22) return;
					match ($slot)
					{
						29 => (($percentage - 10) < 0) ? $percentage = 0 : $percentage -= 10,
						30 => (($percentage - 1) < 0) ? $percentage = 0 : $percentage -= 1,
						31 => $percentage === 0 ?: $modifiedItem->getNamedTag()->setInt('percentage', $percentage),
						32 => (($percentage + 1) > 100) ? $percentage = 100 : $percentage += 1,
						33 => (($percentage + 10) > 100) ? $percentage = 100 : $percentage += 10,
						default => null
					};

					$modifiedItem->setLore(["($percentage%)"]);

					if ($slot === 31)
					{
						$inventory->clearAll();
						$inventory->setContents($savedInventory);
						$percentage = 0;
						$step = 0;
						$inventory->transactionCancel();
						$inventory->setViewOnly(false);
						return;
					}
					return;
				}
				if (!in_array($sourceItem, $savedInventory, true) || !in_array($targetItem, $savedInventory, true))
				{
					$modifiedItem = $targetItem;
					$savedInventory = $inventory->getContents();
					$savedInventory[$slot] = $targetItem;
					$inventory->clearAll();
					$step = 1;
					array_walk($list, fn(Item $item, int $slot) => $inventory->setItem($slot, $item));
					$inventory->setItem(22, $targetItem);
					$inventory->transactionCancel();
					$inventory->setViewOnly(true);
				}
			});

			$item = VanillaBlocks::CHEST()->asItem();

			$form->setCloseListener(function (LinesiaPlayer $player, BaseInventoryCustom $inventory) use ($form, $args, &$item)
			{
				if (!empty($inventory->getContents()))
				{
					$item->setCustomName($args["name"]);
					$item->getNamedTag()->setString('box', $args['name']);
					$player->getInventory()->addItem($item);
					$box = new Box($args['name'], $inventory->getContents());
					Handler::BOX()->addBox($box);
					return;
				}
				$form->send($player);
			});
			$form->send($sender);
		}
	}
}