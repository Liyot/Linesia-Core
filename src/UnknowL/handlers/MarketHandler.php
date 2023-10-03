<?php

namespace UnknowL\handlers;

use customiesdevs\customies\item\CustomiesItemFactory;
use pocketmine\block\Block;
use pocketmine\item\StringToItemParser;
use pocketmine\network\mcpe\protocol\LevelSoundEventPacketV1;
use pocketmine\network\mcpe\protocol\types\LevelSoundEvent;
use pocketmine\utils\Config;
use pocketmine\world\sound\BlockBreakSound;
use pocketmine\world\sound\EntityAttackNoDamageSound;
use pocketmine\world\sound\Sound;
use UnknowL\handlers\dataTypes\MarketData;
use UnknowL\lib\forms\CustomForm;
use UnknowL\lib\forms\CustomFormResponse;
use UnknowL\lib\forms\element\Dropdown;
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
	private array $categories = [];

	public function __construct()
	{
		$this->db = new Config(Linesia::getInstance()->getDataFolder()."data/market/market.yml", Config::YAML);
		parent::__construct();
	}

	protected function loadData(): void
	{
		foreach ($this->db->getNested("market.categories") as $category => $db)
		{
			foreach ($db as $marketName => $value)
			{
				$id = $value["id"];
				$item = StringToItemParser::getInstance()->parse($id);
				if (is_null($item))
				{
					$item = CustomiesItemFactory::getInstance()->get($id);
				}
				$this->categories[$category][$marketName] = new MarketData($item, $value["sellPrice"], $value["description"],
					$value["image"], $marketName, $value["quantities"], $value["buyPrice"]);
			}
		}
	}

	protected function saveData(): void
	{

	}

	final public function getSellable(string $category, string $sellable)
	{
		return $this->categories[$category][$sellable];
	}

	final public function getForm(string $category): MenuForm
	{
		//if (empty($category)) MenuForm::withOptions("");

		$buttons = [];
		foreach ($this->categories[$category] as $name => $data)
		{
			$buttons[] = new Button($data->getName(), Image::path($data->getImage()));
		}

		return new MenuForm(ucfirst($category), "", $buttons,
			function(LinesiaPlayer $player, Button $selected) use  ($category)
			{
				/**@var MarketData $data **/
				$data = array_values(array_filter($this->categories[$category], fn(MarketData $value) => $value->getName() === $selected->text))[0];
				$form = new CustomForm($data->getName(),
					[
						new Label(sprintf("§dItem: §5 %s \n §dDescription: §5 %s.\n §dPrix de vente: §5 %d$ \n §dPrix d'achat: §5 %d",$data->getName(), $data->getDescription(), $data->getSellPrice(), $data->getBuyPrice())),
						new Dropdown('Options:', ["Acheter", "Vendre", "Tout vendre"]),
						new Slider("Quantités", 1, $data->getQuantities()),
					],
					function (LinesiaPlayer $player, CustomFormResponse $response) use  ($data)
					{
						$dropdown = $response->getDropdown()->getSelectedOption();
						if ($dropdown === "Tout vendre")
						{
							$data->sellAll($player);
							return;
						}
						$buy = $dropdown === "Acheter";
						$slide = $response->getSlider();
						$quantities = round($slide->getValue());
						$buy ? $data->buy($player, $quantities) : $data->sell($player, $quantities);
					});
				$player->sendForm($form);
			});
	}

	public function getConfig(): Config
	{
		return $this->db;
	}

	public function getName(): string
	{
		return "Market";
	}
}