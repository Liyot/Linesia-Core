<?php

namespace UnknowL\handlers;

use pocketmine\block\inventory\DoubleChestInventory;
use pocketmine\block\utils\DyeColor;
use pocketmine\item\Item;
use pocketmine\item\VanillaItems;
use pocketmine\scheduler\ClosureTask;
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
use UnknowL\lib\inventoryapi\inventories\SimpleChestInventory;
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
		parent::__construct();
	}

	protected function loadData(): void
	{
		foreach ($this->config->get("items") as $category => $data)
		{
			array_walk($data, function($data, $id)
			{
				$this->items[$data["category"]][$id] = new ShopData($data["player"], $data["name"], $data["price"], $this,
					Item::jsonDeserialize($data["item"]), $data["quantities"], $data["description"], $data["category"], $id);
			});
		}
	}

	protected function saveData(): void
	{
		foreach ($this->items as $category => $data)
		{
			if(!empty($category))
			{
				$config = $this->config;
				array_walk($this->items[$category], function(ShopData $value, int $id) use ($config, $category) {
					$config->setNested("items.".$category, [$id => $value->format()]);
					$config->save();
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
			var_dump($data, $id);
			unset($this->items[$category][$id]);
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

	final public function categoriesForm(string $category): SimpleChestInventory
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
			$pages[$pageCount][] = $shopData;
			++$count;
		}



			array_map(fn(ShopData $data) => $form->addItem($data->getItem()), empty($pages) ? [] : $pages[$actualPages]);
		$form->setClickListener(function (LinesiaPlayer $player, BaseInventoryCustom $inventory, Item $sourceItem, Item $targetItem, int $slot) use ($category, $form, $pageCount, $pages, $actualPages) {
			switch ($slot)
			{
				case 0:
					if(!($actualPages === 0))
					{
						array_map(fn(Item $item) => $inventory->removeItem($item), $inventory->getContents());
						$inventory->setItem(0, VanillaItems::DYE()->setColor(DyeColor::RED())->setCustomName("Page Précédente"));
						$inventory->setItem(53, VanillaItems::DYE()->setColor(DyeColor::GREEN())->setCustomName("Page suivante"));
						$form->onClose($player);
						$form->send($player);
					}
					break;

				case 53:
					if (($actualPages + 1) <=  $pageCount)
					{
						++$actualPages;
						array_map(fn(Item $item) => $inventory->addItem($item), $pages[$actualPages]);
						$form->onClose($player);
						$form->send($player);
					}
					break;
			}
			$form->onClose($player);
			/**@var ShopData $data **/
			var_dump(spl_object_id($sourceItem));
			$data = array_values(array_filter(empty($pages) ? [] : $pages[$pageCount], fn(ShopData $value) => spl_object_id($sourceItem) === spl_object_id($value)));
			if(isset($data[0])){
				$data = $data[0];
				$form = new CustomForm($data->getName(),
					[
						new Label(sprintf("Item: %s \n Description: %s.\n Prix: %d$ !",$data->getItem()->getName(), $data->getDescription(), $data->getPrice())),
						new Slider("Quantités", 1, $data->getQuantities()),
					],
					function (LinesiaPlayer $player, CustomFormResponse $response) use ($form, $data)
					{
						$data->buy($response->getSlider()->getValue(), $player);
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

	public function getName(): string
	{
		return "Shop";
	}
}