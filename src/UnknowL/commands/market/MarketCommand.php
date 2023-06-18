<?php

namespace UnknowL\commands\market;

use pocketmine\command\CommandSender;
use pocketmine\item\Item;
use pocketmine\plugin\Plugin;
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
use UnknowL\Linesia;
use UnknowL\player\LinesiaPlayer;

class MarketCommand extends BaseCommand
{

	private string $category;

	public function __construct()
	{
		$settings = Linesia::getInstance()->getCommandManager()->getSettings("market");
		parent::__construct(Linesia::getInstance(), $settings->getName(), $settings->getDescription(), $settings->getAliases());
	}

	/**
     * @inheritDoc
     */
    protected function prepare(): void
    {
		$this->setPermission("pocketmine.group.user");
		$this->addConstraint(new InGameRequiredConstraint($this));
	}

    /**
     * @var LinesiaPlayer $sender
     */
    public function onRun(CommandSender $sender, string $aliasUsed, array $args): void
    {
		$this->setCategory($sender);
	}

	private function setCategory(LinesiaPlayer $player): void
	{
		$form = MenuForm::withOptions("Catégories", "",["Générale", "Blocks", "Armures", "Epées", "Spécial", "Autres"], function (LinesiaPlayer $player, Button $selected) {
			$this->category = match ($selected->text)
			{
				"Générale" => ShopHandler::CATEGORY_ALL,
				"Blocks" => ShopHandler::CATEGORY_BLOCKS,
				"Armures" => ShopHandler::CATEGORY_ARMORS,
				"Epées" => ShopHandler::CATEGORY_SWORDS,
				"Spécial" => ShopHandler::CATEGORY_SPECIAL,
				"Autres" => ShopHandler::CATEGORY_OTHER
			};
			$player->sendForm(Handler::MARKET()->getForm($this->category));
		});
		$player->sendForm($form);
	}
}