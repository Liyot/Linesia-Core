<?php

namespace UnknowL\commands\shop;

use pocketmine\command\CommandSender;
use pocketmine\item\Item;
use UnknowL\handlers\dataTypes\ShopData;
use UnknowL\lib\commando\args\StringArgument;
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
		$this->addConstraint(new InGameRequiredConstraint($this));
		$this->registerArgument(0, new StringArgument("buy/sell"));
	}

    /**
     * @param LinesiaPlayer $sender
     */
    public function onRun(CommandSender $sender, string $aliasUsed, array $args): void
    {
		$this->sendMainForm($sender, (count($args) === 1) && ($args["sell"]));
	}

	protected function sendMainForm(LinesiaPlayer $player, bool $sell = false): void
	{
		$this->setCategory($player, $sell);
	}

	private function setCategory(LinesiaPlayer $player, bool $sell = false): void
	{
		$form = MenuForm::withOptions("Catégories", "",["Générale", "Blocks", "Armures", "Epées", "Spécial", "Autres"], function (LinesiaPlayer $player, Button $selected) use ($sell) {
			$this->category = match ($selected->text)
			{
				"Générale" => ShopHandler::CATEGORY_ALL,
				"Blocks" => ShopHandler::CATEGORY_BLOCKS,
				"Armures" => ShopHandler::CATEGORY_ARMORS,
				"Epées" => ShopHandler::CATEGORY_SWORDS,
				"Spécial" => ShopHandler::CATEGORY_SPECIAL,
				"Autres" => ShopHandler::CATEGORY_OTHER
			};
			$sell ? $player->sendForm(Linesia::getInstance()->getShopHandler()->categoriesForm($this->category)) : $this->sellForm($player, $this->category);
		});
		$player->sendForm($form);
	}

	protected function sellForm(LinesiaPlayer $player, string $category): void
	{
		//REFAIS TA DISPOSITION STV
		$options = array_map(fn($value) => $value->getName(), $player->getInventory()->getContents());
		sort($options, SORT_NUMERIC);
		$form = new CustomForm("Vendre vos objets", [
			new Label("Renseignez les informations"), new Slider("Quantités", 1, 64),
			new Dropdown("Choissisez votre item", $options),
			new Input("Nom", ""),
			new Input("Prix", "0"),
			new Input("Description", "")],
		function (LinesiaPlayer $player, CustomFormResponse $response) use ($category) {
			$handler = Linesia::getInstance()->getShopHandler();

			$quantities = $response->getSlider()->getValue();
			$itemName = $response->getDropdown()->getSelectedOption();
			$name = $response->getInput()->getValue();
			$price = (int)$response->getInput()->getValue();
			$description = $response->getInput()->getValue();

			/**
			 * @var $item Item
			 */
			$item = array_values(array_filter($player->getInventory()->getContents(), fn(Item $value) => $value->getName() === $itemName))[0];
			if($quantities <= $item->getCount())
			{
				$handler->addSellable(new ShopData($player->getName(), $name, $price, $handler,
					$item, $quantities, $description, $category));
				$player->getInventory()->removeItem($item->setCount($quantities));
				$player->sendMessage("Votre item à été mis en vente");
				return;
			}
			$player->sendMessage("Vérifiez les informations que vous avez saisie");
		});
		$player->sendForm($form);
	}
}