<?php

namespace UnknowL\handlers;

use pocketmine\block\utils\DyeColor;
use pocketmine\block\VanillaBlocks;
use pocketmine\form\Form;
use pocketmine\item\Armor;
use pocketmine\item\Durable;
use pocketmine\item\Item;
use pocketmine\item\ItemBlock;
use pocketmine\item\Pickaxe;
use pocketmine\item\Sword;
use pocketmine\item\VanillaItems;
use pocketmine\nbt\LittleEndianNbtSerializer;
use pocketmine\scheduler\Task;
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
			$data = new ShopData
			(
				$data["player"],
				$data["price"],
				$this,
				Item::nbtDeserialize((new LittleEndianNbtSerializer())->read(base64_decode($data["item"]))->mustGetCompoundTag()),
				(int)$data["duration"],
			);
			$data->id = $data->getItem()->getNamedTag()->getTag("MarketId")->getValue();
			if (!$this->hasExpire($data))
			{
				$this->items[$id] = $data;
				return;
			}
			$this->items["expires"][$id] = $data;
		}
	}

	protected function saveData(): void
	{
		foreach ($this->items as $id => $data)
		{
			$config = $this->config;
			if (is_array($data))
			{
				array_walk($this->items["expires"], function(ShopData $value, int $id) use ($config) {
					$config->set("items", [$id => $value->format()]);
					$config->save();
				});
				unset($this->items["expires"]);
				continue;
			}

			array_walk($this->items, function(ShopData $value, int $id) use ($config) {
				$config->set("items", [$id => $value->format()]);
				$config->save();
			});
			return;
		}
	}

	final public function addSellable(ShopData $data): void
	{
		$data->id = $this->generateId();
		$this->items[$data->getId()] = $data;
		if ($data->getItem()->getNamedTag()->getTag("MarketId") === null)
		{
			$data->getItem()->setNamedTag($data->getItem()->getNamedTag()->setInt("MarketId", $data->getId()));
		}
	}

	final public function hasExpire(ShopData $data): bool
	{
		return $data->getDuration() <= time();
	}

	final public function applyTaxes(LinesiaPlayer $player, int $price): bool
	{
		if ($player->getEconomyManager()->reduce($player->getRank()->getMarketTaxes() * $price))
		{
			$player->sendMessage("§aLes taxes à votre prix de vente à bien été ajouté !");
			return true;
		}
		$player->sendMessage("§cVous n'avez pas assez d'argent pour payer les taxes!");
		return false;
	}

	final public function getItemCount(LinesiaPlayer $player): int
	{

		return count(array_filter($this->items, fn($data) => !is_array($data) && $data->getPlayer() === $player->getName()));
	}

	/**@return ShopData[]*/
	final public function getExpiredItems(LinesiaPlayer $player): array
	{
		$items = [];
        foreach ($this->items["expires"] as $id => $data)
        {
            if ($data->getPlayer() === $player->getName())
            {
                $items[] = $data;
            }
        }
        return $items;
	}

	final public function removeSellable(int $id, bool $expired = false): void
	{
		foreach ($expired ? $this->items["expires"] : $this->items as $category => $data)
		{
			if ($expired) {
				unset($this->items["expires"][$id]);
				continue;
			}
			unset($this->items[$id]);
		}
	}

	final public function generateId(): int
	{
		$rand = random_int(-2147483648, 2147483647);
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
			45 => VanillaItems::BONE()->setCustomName("Items Expirés"),
			48 => VanillaItems::DYE()->setColor(DyeColor::RED())->setCustomName("<- Page précédente"),
			49 => VanillaBlocks::JUKEBOX()->asItem()->setCustomName("Page 1"),
			50 => VanillaItems::DYE()->setColor(DyeColor::GREEN())->setCustomName("Page suivante ->"),
			53 => VanillaItems::WRITABLE_BOOK()->setCustomName("Catégorie")
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
			if (!is_array($data))
			{
				if($count === 45)
				{
					++$pageCount;
					$count = 0;
				}
				$pages[$pageCount][$id] = $data;
				$data->getItem()->setNamedTag($data->getItem()->getNamedTag()->setInt('MarketId', $id));
				$data->getItem()->setLore([$this->formatLeftTime( $data->getDuration() - time()) , $data->getPlayer(), $data->getPrice() . "$"]);
				++$count;
			}
		}

		count($pages) < 1 ?: array_map(fn(ShopData $data) => $form->addItem($data->getItem()->setCount($data->getQuantities())), $pages[$actualPages]);

		$baseListener = function (LinesiaPlayer $player, BaseInventoryCustom $inventory, Item $sourceItem, Item $targetItem, int $slot) use (&$previousPageCount, &$sortedPages, &$baseContent, &$category, &$form, &$pageCount, &$pages, &$actualPages) {
			switch ($slot)
			{
				case 45:
					if (isset($this->items["expires"]))
					{
						$inventory->clearAll();
						if (empty($category))
						{
							array_map(fn(ShopData $data) => $inventory->addItem($data->getItem()), $this->getExpiredItems($player));
							$inventory->setClickListener(function (LinesiaPlayer $player, BaseInventoryCustom $inventory, Item $sourceItem, Item $targetItem, int $slot)
							{
								if ($sourceItem->getNamedTag()->getTag("MarketId") !== null)
								{
									if ($player->getInventory()->canAddItem($sourceItem))
									{
										$inventory->removeItem($sourceItem);
										$this->removeSellable($sourceItem->getNamedTag()->getInt("MarketId"), true);
										$nbt = $sourceItem->getNamedTag();
										$nbt->removeTag("MarketId");
										$sourceItem->setNamedTag($nbt);
										$sourceItem->setLore([""]);
										$player->getInventory()->addItem($sourceItem);
										return;
									}
									$player->sendToastNotification("Impossible !", "§cVous n'avez pas assez d'espace dans votre inventaire !");
								}
							});
							return;
						}

						$category = "";
						empty($pages[$actualPages]) ? $inventory->setContents([]) : array_map(fn(ShopData $data) => $inventory->addItem($data->getItem()), $pages[$actualPages]);
						array_walk($baseContent, fn(Item $item, int $slot) => $inventory->setItem($slot, $item));
						$inventory->rewindClickListener();
						return;
					}
					$player->sendToastNotification("Impossible!", "§cVous n'avez pas d'item dont la vente à expirée.");
					break;

				case 48:
					if(!($actualPages === 0))
					{
						array_map(fn(Item $item) => $inventory->removeItem($item), array_slice($inventory->getContents(), -9));
						$inventory->setItem(49, VanillaBlocks::JUKEBOX()->asItem()->setCustomName("Page $actualPages"));
						$actualPages--;
						array_map(fn(ShopData $data) => $form->addItem($data->getItem()->setCount($data->getQuantities())), empty($category) ? $pages[$actualPages] : $sortedPages[$actualPages]);
					}
					break;

				case 49:
					break;

				case 50:
					if (($actualPages + 1) <=  $pageCount)
					{
						array_map(fn(Item $item) => $inventory->removeItem($item), array_slice($inventory->getContents(), -9));
						++$actualPages;
						$inventory->setItem(49, VanillaBlocks::JUKEBOX()->asItem()->setCustomName("Page $actualPages"));
						array_map(fn(ShopData $data) => $form->addItem($data->getItem()->setCount($data->getQuantities())), empty($category) ? $pages[$actualPages] ?? [] : $sortedPages[$actualPages] ?? []);
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

						$inventory->setClickListener(function (LinesiaPlayer $player, BaseInventoryCustom $inventory, Item $sourceItem, Item $targetItem, int $slot) use (&$pageCount, &$previousPageCount, $baseContent, $actualPages, $form, &$category, &$sortedPages) {

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
					$category = "";
					empty($pages[$actualPages]) ? $inventory->setContents([]) : array_map(fn(ShopData $data) => $inventory->addItem($data->getItem()), $pages[$actualPages]);
					array_walk($baseContent, fn(Item $item, int $slot) => $inventory->setItem($slot, $item));
					break;

				default:
					if ($sourceItem->getNamedTag()->getTag("MarketId") !== null)
					{
						$id = $sourceItem->getNamedTag()->getTag("MarketId")->getValue();
						$data = $pages[$pageCount][$id] ?? $sortedPages[$pageCount][$id];
						if(!is_null($data))
						{
							$form->onClose($player);
							/**@var ShopData $data **/
							$form = new CustomForm($data->getItem()->getName(),
								[
									new Label(sprintf("Vendeur: %s \n Item: %s \n Prix: %d$ !",$data->getPlayer(), $data->getItem()->getName(), $data->getPrice())),
									new Slider("Quantités", 1, $data->getQuantities()),
								],

								function (LinesiaPlayer $player, CustomFormResponse $response) use ($form, $data)
								{
									$slider = $response->getSlider();
									$quantities = round($slider->getValue());
									$data->buy($quantities, $player);
								});

							Linesia::getInstance()->getScheduler()->scheduleDelayedTask(new class($form, $player) extends Task
							{
								public function __construct(private Form $form, private LinesiaPlayer $player){}
								public function onRun(): void {$this->player->sendForm($this->form);}
							}, 30);
						}
						break;
					}
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
			if (is_array($data)) continue;
			$item = $data->getItem();
			match (true)
			{
				$item instanceof Armor && ($category === ShopHandler::CATEGORY_ARMORS) => $result[$id] = $item,
				$item instanceof ItemBlock && ($category === ShopHandler::CATEGORY_BLOCKS) => $result[$id] = $item,
				$item instanceof Sword && ($category === ShopHandler::CATEGORY_SWORDS) => $result[$id] = $item,
                $item instanceof Durable && ($category === ShopHandler::CATEGORY_SPECIAL) => $result[$id] = $item,
                default => !($category === ShopHandler::CATEGORY_OTHER) ?: $result[$id] = $item,
			};
		}

		return $result;
	}

	private function formatLeftTime(int $seconds): string
	{
		$days = floor($seconds / 86400);
        $seconds -= $days * 86400;
        $hours = floor($seconds / 3600);
        $seconds -= $hours * 3600;
        $minutes = floor($seconds / 60);
        $seconds -= $minutes * 60;
        $seconds = $seconds < 10? "0{$seconds}" : $seconds;
        return "{$days} jours, {$hours} heures, {$minutes} minutes, {$seconds} secondes";
	}

//	final public function categoriesForm(string $category): SimpleChestInventory
//	{
//		$form = InventoryAPI::createDoubleChest(true);
//		$form->setItem(0, VanillaItems::DYE()->setColor(DyeColor::RED())->setCustomName("Page Précédente"));
//		$form->setItem(53, VanillaItems::DYE()->setColor(DyeColor::GREEN())->setCustomName("Page suivante"));
//		$form->setName(ucfirst($category));
//
//		$count = 0;
//		$pageCount = 0;
//		$pages = [];
//		$actualPages = 0;
//
//		/**
//		 * @var ShopData $shopData
//		 */
//		foreach ($this->items[$category] as $id => $shopData)
//		{
//			if($count === 52) ++$pageCount;
//			$pages[$pageCount][] = $shopData;
//			++$count;
//		}
//
//
//
//			array_map(fn(ShopData $data) => $form->addItem($data->getItem()), empty($pages) ? [] : $pages[$actualPages]);
//		$form->setClickListener(function (LinesiaPlayer $player, BaseInventoryCustom $inventory, Item $sourceItem, Item $targetItem, int $slot) use ($category, $form, $pageCount, $pages, $actualPages) {
//			switch ($slot)
//			{
//				case 0:
//					if(!($actualPages === 0))
//					{
//						array_map(fn(Item $item) => $inventory->removeItem($item), $inventory->getContents());
//						$inventory->setItem(0, VanillaItems::DYE()->setColor(DyeColor::RED())->setCustomName("Page Précédente"));
//						$inventory->setItem(53, VanillaItems::DYE()->setColor(DyeColor::GREEN())->setCustomName("Page suivante"));
//						$form->onClose($player);
//						$form->send($player);
//					}
//					break;
//
//				case 53:
//					if (($actualPages + 1) <=  $pageCount)
//					{
//						++$actualPages;
//						array_map(fn(Item $item) => $inventory->addItem($item), $pages[$actualPages]);
//						$form->onClose($player);
//						$form->send($player);
//					}
//					break;
//			}
//			$form->onClose($player);
//			/**@var ShopData $data **/
//			$data = array_values(array_filter(empty($pages) ? [] : $pages[$pageCount], fn(ShopData $value) => spl_object_id($sourceItem) === spl_object_id($value)));
//			if(isset($data[0])){
//				$data = $data[0];
//				$form = new CustomForm($data->getName(),
//					[
//						new Label(sprintf("Item: %s \n Description: %s.\n Prix: %d$ !",$data->getItem()->getName(), $data->getDescription(), $data->getPrice())),
//						new Slider("Quantités", 1, $data->getQuantities()),
//					],
//					function (LinesiaPlayer $player, CustomFormResponse $response) use ($form, $data)
//					{
//						$data->buy($response->getSlider()->getValue(), $player);
//					});
//				$player->sendForm($form);
//			}
//		});
//		return $form;
//	}

	public function __destruct()
	{
		$this->saveData();
	}

	public function getName(): string
	{
		return "Shop";
	}
}