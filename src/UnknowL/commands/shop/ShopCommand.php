<?php

namespace UnknowL\commands\shop;

use pocketmine\command\CommandSender;
use pocketmine\item\Item;
use pocketmine\item\VanillaItems;
use pocketmine\scheduler\ClosureTask;
use UnknowL\handlers\dataTypes\ShopData;
use UnknowL\handlers\Handler;
use UnknowL\handlers\ShopHandler;
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
		$this->addConstraint(new InGameRequiredConstraint($this));
		$this->registerArgument(0, new StringArgument("buy/sell"));
	}

    /**
     * @param LinesiaPlayer $sender
     */
    public function onRun(CommandSender $sender, string $aliasUsed, array $args): void
    {
		$this->sendMainForm($sender, (count($args) === 1) && ($args["buy/sell"] === "sell"));
	}

	protected function sendMainForm(LinesiaPlayer $player, bool $sell = false): void
	{
		$form = MenuForm::withOptions("Que souhaitez vous faire?", "", ["Vendre", "Acheter"],
			fn (LinesiaPlayer $player, Button $selected) => $this->setCategory($player, $selected->text === "Vendre"));
		$player->sendForm($form);
	}

	private function setCategory(LinesiaPlayer $player, bool $sell): void
	{
		$form = InventoryAPI::createSimpleChest(true)
			->setName("Catégories");
		$form->addItem(
			VanillaItems::SLIMEBALL()->setCustomName("Générale"),
				VanillaItems::SLIMEBALL()->setCustomName("Blocks"),
				VanillaItems::SLIMEBALL()->setCustomName("Armures"),
				VanillaItems::SLIMEBALL()->setCustomName("Epées"),
				VanillaItems::SLIMEBALL()->setCustomName("Spécial"),
				VanillaItems::SLIMEBALL()->setCustomName("Autres"));
		$form->setClickListener(function (LinesiaPlayer $player, BaseInventoryCustom $inventory, Item $sourceItem, Item $targetItem, int $slot) use ($form, $sell) {
			$this->category = match ($sourceItem->getCustomName())
			{
				"Blocks" => ShopHandler::CATEGORY_BLOCKS,
				"Armures" => ShopHandler::CATEGORY_ARMORS,
				"Epées" => ShopHandler::CATEGORY_SWORDS,
				"Spécial" => ShopHandler::CATEGORY_SPECIAL,
				"Autres" => ShopHandler::CATEGORY_OTHER,
				"Générale" => ShopHandler::CATEGORY_ALL,

			};
			$form->onClose($player);
			$sell ? $this->sellForm($player, $this->category) : Handler::SHOP()->categoriesForm($this->category)->send($player);

		});
		$form->send($player);
	}

	protected function sellForm(LinesiaPlayer $player, string $category): void
	{
		//REFAIS TA DISPOSITION STV
		$options = array_map(fn($value) => $value->getName() , $player->getInventory()->getContents());
		sort($options, SORT_NUMERIC);
		$form = new CustomForm("Vendre vos objets", [
			new Label("Renseignez les informations"), new Slider("Quantités", 1, 64),
			new Dropdown("Choissisez votre item", $options),
			new Input("Nom", ""),
			new Input("Prix", "0"),
			new Input("Description", "")],
		function (LinesiaPlayer $player, CustomFormResponse $response) use ($category) {
			$handler = Handler::SHOP();
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