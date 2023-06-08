<?php

namespace UnknowL\handlers;

use pocketmine\block\inventory\DoubleChestInventory;
use pocketmine\item\Item;
use pocketmine\item\VanillaItems;
use pocketmine\utils\Config;
use UnknowL\handlers\dataTypes\ShopData;
use UnknowL\lib\forms\CustomForm;
use UnknowL\lib\forms\CustomFormResponse;
use UnknowL\lib\forms\element\Label;
use UnknowL\lib\forms\element\Slider;
use UnknowL\lib\forms\element\StepSlider;
use UnknowL\lib\forms\menu\Button;
use UnknowL\lib\forms\MenuForm;
use UnknowL\lib\inventoryapi\inventories\BaseInventoryCustom;
use UnknowL\lib\inventoryapi\inventories\DoubleInventory;
use UnknowL\lib\inventoryapi\InventoryAPI;
use UnknowL\Linesia;
use UnknowL\player\LinesiaPlayer;

final class ShopHandler extends Handler
{

	public const CATEGORY_ALL = "all";
	public const CATEGORY_BLOCKS = "blocks";
	public const CATEGORY_ARMORS = "armors";
	public const CATEGORY_SWORDS = "swords";
	public const CATEGORY_SPECIAL = "special";
	public const CATEGORY_OTHER = "other";

	/**
	 * @phpstan-param $items list<string<list<ShopData>>>
	 */
	protected array $items = ["all" => [], "blocks" => [], "armors" => [], "swords" => [], "special" => [], "other" => []];

	private Config $config;

	public function __construct()
	{
		$this->config = new Config(Linesia::getInstance()->getDataFolder()."data/shop/shop.json", Config::JSON);
		$this->loadData();
	}

	protected function loadData(): void
	{
		foreach ($this->config->get("items") as $category => $data)
		{
			array_walk($data, function($data, $id)
			{
				$this->items[$data["category"]][$id] = new ShopData($data["player"], $data["name"], $data["price"], $this,
					Item::jsonDeserialize($data["item"]), $data["quantities"], $data["description"], $data["category"]);
			});
		}
	}

	protected function saveData(): void
	{
		foreach ($this->items as $category => $data)
		{
			if(!empty($category))
			{
				array_walk($this->items[$category], function(ShopData $value, int $id) use ($category) {
					$this->config->setNested("items.".$category, [$id => $value->format()]);
					$this->config->save();
				});
				return;
			}
		}
	}

	final public function addSellable(ShopData $data): void
	{
		$this->items[$data->getCategory()][$data->getId()] = $data;
	}

	final public function removeSellable(int $id): void
	{
		foreach ($this->items as $category => $data)
		{
			unset($data[$id]);
		}
	}

	final public function generateId(string $category): int
	{
		$rand = random_int(0, PHP_INT_MAX);
		if(isset($this->items[$category][$rand]))
		{
			$this->generateId($category);
		}
		return $rand;
	}

	final public function categoriesForm(string $category): DoubleInventory
	{
		$form = InventoryAPI::createDoubleChest(true);
		$form->setItem(0, VanillaItems::RED_DYE()->setCustomName("Page Précédente"));
		$form->setItem(53, VanillaItems::GREEN_DYE()->setCustomName("Page suivante"));
		$form->setName(ucfirst($category));

		$count = 0;
		$pageCount = 0;
		$pages = [];
		$actualPages = 0;

		/**
		 * @var ShopData $shopData
		 */
		foreach ($this->items[$category] as $id => $shopData)
		{
			if($count === 52) ++$pageCount;
			$pages[$pageCount][] = $shopData->getItem();
			++$count;
		}

		array_map(fn(Item $item) => $form->addItem($item), $pages[$actualPages]);
		$form->setClickListener(function (LinesiaPlayer $player, BaseInventoryCustom $inventory, Item $sourceItem, Item $targetItem, int $slot) use ($category, $form, $pageCount, $pages, $actualPages) {
			switch ($slot)
			{
				case 0:
					if(!($actualPages === 0))
					{
						array_map(fn(Item $item) => $inventory->removeItem($item), $inventory->getContents());
						$inventory->setItem(0, VanillaItems::RED_DYE()->setCustomName("Page Précédente"));
						$inventory->setItem(53, VanillaItems::GREEN_DYE()->setCustomName("Page suivante"));
					}
					break;

				case 53:
					if (!($actualPages === count($pages)))
					{
						++$pageCount;
						array_map(fn(Item $item) => $inventory->addItem($item), $pages[$pageCount]);
					}
					break;


			}
			$form->onClose($player);
			/**@var ShopData $data **/
			$data = array_values(array_filter($this->items[$category], fn(ShopData $value) => $targetItem === $value->getItem()));
			if(isset($data[0])){
				$form = new CustomForm($data->getName(),
					[
						new Label(sprintf("Item: %s \n Description: %s.\n Prix: %d$ !",$data->getItem()->getName(), $data->getDescription(), $data->getPrice())),
						new Slider("Quantités", 1, $data->getQuantities()),
					],
					function (LinesiaPlayer $player, CustomFormResponse $response) use ($form, $data)
					{
						$data->buy($response->getSlider()->getValue(), $player);
						$form->send($player);
					});
				$player->sendForm($form);
			}
		});
		return $form;
	}

	public function __destruct()
	{
		$this->saveData();
	}
}