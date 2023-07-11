<?php

namespace UnknowL\handlers;

use pocketmine\block\utils\DyeColor;
use pocketmine\block\VanillaBlocks;
use pocketmine\item\Armor;
use pocketmine\item\Durable;
use pocketmine\item\Item;
use pocketmine\item\ItemBlock;
use pocketmine\item\Sword;
use pocketmine\item\VanillaItems;
use pocketmine\nbt\LittleEndianNbtSerializer;
use pocketmine\network\mcpe\convert\TypeConverter;
use pocketmine\network\mcpe\protocol\types\BlockPosition;
use pocketmine\network\mcpe\protocol\UpdateBlockPacket;
use pocketmine\utils\Config;
use UnknowL\handlers\dataTypes\ShopData;
use UnknowL\lib\forms\CustomForm;
use UnknowL\lib\forms\CustomFormResponse;
use UnknowL\lib\forms\element\Label;
use UnknowL\lib\forms\element\Slider;
use UnknowL\lib\inventoryapi\inventories\BaseInventoryCustom;
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
	 * @var  $items ShopData[]
	 */
	protected array $items = [];

	private Config $config;

	public function __construct()
	{
		$this->config = new Config(Linesia::getInstance()->getDataFolder()."data/shop/shop.json", Config::JSON);
		parent::__construct();
	}

	protected function loadData(): void
	{
		foreach ($this->config->get("items") as $id => $data)
		{
			$this->items[$id] = new ShopData
			(
				$data["player"],
				$data["price"],
				$this,
				Item::nbtDeserialize((new LittleEndianNbtSerializer())->read($data["item"])->mustGetCompoundTag()),
				(int)$data["duration"],
			);
		}
	}

	protected function saveData(): void
	{
		foreach ($this->items as $id => $data)
		{
			$config = $this->config;
			array_walk($this->items, function(ShopData $value, int $id) use ($config) {
				$config->set("items", [$id => $value->format()]);
				$config->save();
			});
			return;
		}
	}

	final public function addSellable(ShopData $data): void
	{
		$this->items[$data->getId()] = $data;
	}

	final public function removeSellable(int $id): void
	{
		foreach ($this->items as $category => $data)
		{
			unset($this->items[$category][$id]);
		}
	}

	final public function generateId(): int
	{
		$rand = random_int(PHP_INT_MIN, PHP_INT_MAX);
		if(isset($this->items[$rand]))
		{
			$this->generateId();
		}
		return $rand;
	}

	final public function getForm(): SimpleChestInventory
	{
		$form = InventoryAPI::createDoubleChest(true);

		$baseContent = [
			48 => VanillaItems::DYE()->setColor(DyeColor::RED()),
			49 => VanillaBlocks::JUKEBOX()->asItem()->setCustomName("Page 1"),
			50 => VanillaItems::DYE()->setColor(DyeColor::GREEN()),
			53 => VanillaItems::WRITABLE_BOOK()
		];

		$form->setContents($baseContent);


		$count = 0;
		$previousPageCount = 0;
		$pageCount = 0;
		$pages = [];
		$actualPages = 0;
		$category = "";

		$sortedPages = [];

		foreach ($this->items as $id => $data)
		{
			if($count === 45)
			{
				++$pageCount;
				$count = 0;
			}
			 $pages[$pageCount][$id] = $data;
			++$count;
		}

		count($pages) < 1 ?: array_map(fn(ShopData $data) => $form->addItem($data->getItem()->setCount($data->getQuantities())), $pages[$actualPages]);

		$baseListener = function (LinesiaPlayer $player, BaseInventoryCustom $inventory, Item $sourceItem, Item $targetItem, int $slot) use (&$previousPageCount, &$sortedPages, &$baseContent, &$category, &$form, &$pageCount, &$pages, &$actualPages) {
			switch ($slot)
			{
				case 48:
					if(!($actualPages === 0))
					{
						array_map(fn(Item $item) => $inventory->removeItem($item), array_slice($inventory->getContents(), -9));
						$inventory->setItem(49, VanillaBlocks::JUKEBOX()->asItem()->setCustomName("Page $actualPages"));
						$actualPages--;
						array_map(fn(ShopData $data) => $form->addItem($data->getItem()->setCount($data->getQuantities())), empty($category) ? $pages[$actualPages] : $sortedPages[$actualPages]);
					}
					break;

				case 50:
					if (($actualPages + 1) <=  $pageCount)
					{
						array_map(fn(Item $item) => $inventory->removeItem($item), array_slice($inventory->getContents(), -9));
						++$actualPages;
						$inventory->setItem(49, VanillaBlocks::JUKEBOX()->asItem()->setCustomName("Page $actualPages"));
						array_map(fn(ShopData $data) => $form->addItem($data->getItem()->setCount($data->getQuantities())), empty($category) ? $pages[$actualPages] : $sortedPages[$actualPages]);
					}
					break;

				case 53:
					$inventory->clearAll();
					if (empty($category))
					{
						$inventory->addItem(
							VanillaItems::SLIMEBALL()->setCustomName("Blocks"),
							VanillaItems::SLIMEBALL()->setCustomName("Armures"),
							VanillaItems::SLIMEBALL()->setCustomName("Epées"),
							VanillaItems::SLIMEBALL()->setCustomName("Spécial"),
							VanillaItems::SLIMEBALL()->setCustomName("Autres"));

						$form->setClickListener(function (LinesiaPlayer $player, BaseInventoryCustom $inventory, Item $sourceItem, Item $targetItem, int $slot) use (&$pageCount, &$previousPageCount, $baseContent, $actualPages, $form, &$category, &$sortedPages) {

							$category = match ($sourceItem->getCustomName())
							{
								"Blocks" => ShopHandler::CATEGORY_BLOCKS,
								"Armures" => ShopHandler::CATEGORY_ARMORS,
								"Epées" => ShopHandler::CATEGORY_SWORDS,
								"Spécial" => ShopHandler::CATEGORY_SPECIAL,
								"Autres" => ShopHandler::CATEGORY_OTHER,
							};

							$sort = $this->sortByCategory($category);
							$inventory->rewindClickListener();
							$sortedPages = array_chunk($sort, 45);
							$actualPages = 0;

							$inventory->setContents(empty($sortedPages) ? [] : $sortedPages[$actualPages]);
							$previousPageCount = $pageCount;
							$pageCount = count($sortedPages);
							array_walk($baseContent, fn(Item $item, int $slot) => $inventory->setItem($slot, $item));
						});
						return;
					}
					$actualPages = 0;
					$pageCount = $previousPageCount;
					$inventory->setContents($pages[$actualPages]);
					array_walk($baseContent, fn(Item $item, int $slot) => $inventory->setItem($slot, $item));
					break;

				default:
					$form->onClose($player);
					$data = array_values(array_filter($pages[$actualPages], fn(ShopData $value) => spl_object_id($sourceItem) === spl_object_id($value)));
					var_dump($data);
					if(isset($data[0])){
						/**@var ShopData $data **/
						$data = $data[0];
						$form = new CustomForm($data->getItem()->getName(),
							[
								new Label(sprintf("Item: %s \n Prix: %d$ !",$data->getItem()->getName(), $data->getPrice())),
								new Slider("Quantités", 1, $data->getQuantities()),
							],
							function (LinesiaPlayer $player, CustomFormResponse $response) use ($form, $data)
							{
								$data->buy($response->getSlider()->getValue(), $player);
							});
						$player->sendForm($form);
					}
					break;
			}
		};
		$form->setClickListener($baseListener);
		return $form;
	}

	private function sortByCategory(string $category): array
	{
		$result = [];

		foreach ($this->items as $id => $data)
		{
			match (true)
			{
				$data->getItem() instanceof Armor && ($category === ShopHandler::CATEGORY_ARMORS) => $result[$id] = $data->getItem(),
				$data->getItem() instanceof ItemBlock && ($category === ShopHandler::CATEGORY_BLOCKS) => $result[$id] = $data->getItem(),
				$data->getItem() instanceof Sword && ($category === ShopHandler::CATEGORY_SWORDS) => $result[$id] = $data->getItem(),
                $data->getItem() instanceof Durable && ($category === ShopHandler::CATEGORY_SPECIAL) => $result[$id] = $data->getItem(),
                default => !($category === ShopHandler::CATEGORY_OTHER) ?: $result[$id] = $data->getItem(),
			};
		}

		return $result;
	}
	final public function categoriesForm(string $category): SimpleChestInventory
	{
		$form = InventoryAPI::createDoubleChest(true);
		$form->setItem(0, VanillaItems::DYE()->setColor(DyeColor::RED())->setCustomName("Page Précédente"));
		$form->setItem(53, VanillaItems::DYE()->setColor(DyeColor::GREEN())->setCustomName("Page suivante"));
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