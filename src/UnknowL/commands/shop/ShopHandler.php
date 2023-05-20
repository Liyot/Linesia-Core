<?php

namespace UnknowL\commands\shop;

use pocketmine\item\Item;
use pocketmine\utils\Config;
use UnknowL\lib\forms\CustomForm;
use UnknowL\lib\forms\CustomFormResponse;
use UnknowL\lib\forms\element\Label;
use UnknowL\lib\forms\element\Slider;
use UnknowL\lib\forms\element\StepSlider;
use UnknowL\lib\forms\menu\Button;
use UnknowL\lib\forms\MenuForm;
use UnknowL\Linesia;
use UnknowL\player\LinesiaPlayer;

final class ShopHandler
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

	private function loadData(): void
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

	private function saveData(): void
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

	final public function categoriesForm(string $category): MenuForm
	{
		$form = MenuForm::withOptions(ucfirst($category), "", array_map(fn(ShopData $data) => $data->getName(), $this->items[$category]),
			function(LinesiaPlayer $player, Button $selected) use ($category)
			{
				/**@var ShopData $data **/
				$data = array_values(array_filter($this->items[$category], fn(ShopData $value) => $value->getName() === $selected->text))[0];
				$form = new CustomForm($data->getName(),
					[
					new Label(sprintf("Item: %s \n Description: %s.\n Prix: %d$ !",$data->getItem()->getName(), $data->getDescription(), $data->getPrice())),
					new Slider("Quantités", 1, $data->getQuantities()),
					],
					function (LinesiaPlayer $player, CustomFormResponse $response) use ($data)
				{
					$data->buy($response->getSlider()->getValue(), $player);
				});
				$player->sendForm($form);
			});

		return $form;
	}

	public function __destruct()
	{
		$this->saveData();
	}
}