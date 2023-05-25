<?php

namespace UnknowL\handlers;

use pocketmine\data\bedrock\EnchantmentIdMap;
use pocketmine\data\bedrock\EnchantmentIds;
use pocketmine\item\enchantment\Enchantment;
use pocketmine\item\enchantment\EnchantmentInstance;
use pocketmine\item\ItemFactory;
use pocketmine\network\mcpe\protocol\types\recipe\FurnaceRecipeBlockName;
use pocketmine\utils\Config;
use UnknowL\handlers\dataTypes\MarketData;
use UnknowL\handlers\dataTypes\ShopData;
use UnknowL\lib\forms\CustomForm;
use UnknowL\lib\forms\CustomFormResponse;
use UnknowL\lib\forms\element\Label;
use UnknowL\lib\forms\element\Slider;
use UnknowL\lib\forms\menu\Button;
use UnknowL\lib\forms\menu\Image;
use UnknowL\lib\forms\MenuForm;
use UnknowL\Linesia;
use UnknowL\player\LinesiaPlayer;

class MarketHandler extends Handler
{

	private Config $db;

	/**
	 * @phpstan-param $categories list<string<list<MarketData>>>
	 */
	private array $categories = ["all" => [], "blocks" => [], "armors" => [], "swords" => [], "special" => [], "other" => []];

	public function __construct()
	{
		$this->db = new Config(Linesia::getInstance()->getDataFolder()."data/market/market.yml", Config::YAML);
		$this->loadData();
	}

	protected function loadData(): void
	{

		foreach ($this->db->getNested("market.categories") as $category => $name)
		{
			$name = array_keys($name)[0];
			$value = $this->db->getNested("market.categories")[$category][$name];
			$item = ItemFactory::getInstance()->get(($str = explode(":", $value["id"]))[0], $str[1]);
			$enchant = new EnchantmentInstance(EnchantmentIdMap::getInstance()->fromId(($str = $value["enchant"])[0]), (int)$str[1]);
			$this->categories[$category][$name] = new MarketData($item, $enchant, $value["price"], $value["description"],
				$value["image"], $name, $value["quantities"]);
		}
	}

	protected function saveData(): void
	{}

	final public function getSellable(string $category, string $sellable)
	{
		return $this->categories[$category][$sellable];
	}

	final public function getForm(string $category): MenuForm
	{
		$buttons = array_values(array_map(function(MarketData $data) {
			return new Button($data->getName(), Image::path($data->getImage()));
		}, $this->categories[$category]));

		var_dump($buttons);
		$form = new MenuForm(ucfirst($category), "", $buttons,
			function(LinesiaPlayer $player, Button $selected) use ($category)
			{
				/**@var MarketData $data **/
				$data = array_values(array_filter($this->categories[$category], fn(MarketData $value) => $value->getName() === $selected->text))[0];
				$form = new CustomForm($data->getName(),
					[
						new Label(sprintf("Item: %s \n Description: %s.\n Prix: %d$ !",$data->getName(), $data->getDescription(), $data->getPrice())),
						new Slider("Quantités", 1, $data->getQuantities()),
					],
					function (LinesiaPlayer $player, CustomFormResponse $response) use ($data)
					{
						$data->buy($player, $data, $response->getSlider()->getValue());
					});
				$player->sendForm($form);
			});

		return $form;
	}
}
