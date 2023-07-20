<?php

namespace UnknowL\commands\shop;

use pocketmine\command\CommandSender;
use pocketmine\item\Item;
use pocketmine\item\VanillaItems;
use UnknowL\commands\shop\sub\ShopBuyCommand;
use UnknowL\commands\shop\sub\ShopSellCommand;
use UnknowL\handlers\dataTypes\ShopData;
use UnknowL\handlers\Handler;
use UnknowL\handlers\ShopHandler;
use UnknowL\lib\commando\BaseCommand;
use UnknowL\lib\commando\constraint\InGameRequiredConstraint;
use UnknowL\lib\forms\CustomForm;
use UnknowL\lib\forms\CustomFormResponse;
use UnknowL\lib\forms\element\Dropdown;
use UnknowL\lib\forms\element\Input;
use UnknowL\lib\forms\element\Label;
use UnknowL\lib\forms\element\Slider;
use UnknowL\lib\forms\menu\Button;
use UnknowL\lib\forms\MenuForm;
use UnknowL\lib\inventoryapi\inventories\BaseInventoryCustom;
use UnknowL\lib\inventoryapi\InventoryAPI;
use UnknowL\Linesia;
use UnknowL\player\LinesiaPlayer;

class ShopCommand extends BaseCommand
{

	private string $category;

	public function __construct()
	{
		$settings = Linesia::getInstance()->getCommandManager()->getSettings("shop");
		parent::__construct(Linesia::getInstance(), $settings->getName(), $settings->getDescription(), $settings->getAliases());
	}

	protected function prepare(): void
    {
		$this->setPermission("pocketmine.group.user");
		$this->registerSubCommand(new ShopSellCommand());
		$this->registerSubCommand(new ShopBuyCommand());
		$this->addConstraint(new InGameRequiredConstraint($this));
	}

    /**
     * @param LinesiaPlayer $sender
     */
    public function onRun(CommandSender $sender, string $aliasUsed, array $args): void
    {
		Handler::SHOP()->getForm()->send($sender);
		//$this->sendMainForm($sender, (count($args) === 1) && ($args["buy/sell"] === "sell"));
	}
}