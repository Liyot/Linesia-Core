<?php

namespace UnknowL\casino\types;

use pocketmine\block\utils\DyeColor;
use pocketmine\block\VanillaBlocks;
use UnknowL\lib\forms\CustomForm;
use UnknowL\lib\forms\CustomFormResponse;
use UnknowL\lib\forms\element\Dropdown;
use UnknowL\lib\forms\element\Input;
use UnknowL\lib\inventoryapi\InventoryAPI;
use UnknowL\Linesia;
use UnknowL\player\LinesiaPlayer;
use UnknowL\task\RouletteTask;

class Roulette extends CasinoGame
{

	public function __construct()
	{
		$this->mise = 0;
	}

    public function getName(): string
    {
		return "Roulette";
	}

    public function getDescription(): string
    {
		return "Tenter de gagner le double de votre mise en tombant sur la bonne couleur";
	}

	public function start(LinesiaPlayer $player, int $mise): void
	{
		$roulette = [];
		for($i = 0; $i <= 36; $i++){
			$color = !$i ? VanillaBlocks::GLAZED_TERRACOTTA()->setColor(DyeColor::GREEN()): match ($i % 2)
			{
				0 => VanillaBlocks::GLAZED_TERRACOTTA(),
				default => VanillaBlocks::GLAZED_TERRACOTTA()->setColor(DyeColor::RED())
			};
			$roulette[] = $color->asItem();
		}

		$form = new CustomForm("Choississez votre mise", [new Input("Mise:" ,"", 1), new Dropdown("Couleur", ["Rouge", 'Noir', 'Vert'])]
			, function (LinesiaPlayer $player, CustomFormResponse $response) use ($roulette) {
			$input = $response->getInput();
			$dropdown = $response->getDropdown();
			$inventory = InventoryAPI::createSimpleChest(true);
			$inventory->send($player);
			$block = match ($dropdown->getSelectedOption())
			{
				"Rouge" => VanillaBlocks::GLAZED_TERRACOTTA()->setColor(DyeColor::RED())->asItem(),
				'Noir' => VanillaBlocks::GLAZED_TERRACOTTA()->asItem(),
				'Vert' => VanillaBlocks::GLAZED_TERRACOTTA()->setColor(DyeColor::GREEN())->asItem()
			};
			Linesia::getInstance()->getScheduler()->scheduleRepeatingTask(new RouletteTask($inventory,$input->getValue(), $roulette, $block, $player), 5);
		});
		$player->sendForm($form);
	}

	public function win(LinesiaPlayer $player, int $gain): void {/*empty*/}

	public function loose(LinesiaPlayer $player): void {/*empty*/}
}