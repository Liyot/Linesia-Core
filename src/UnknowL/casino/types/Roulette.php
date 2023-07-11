<?php

namespace UnknowL\casino\types;

use pocketmine\block\Concrete;
use pocketmine\block\utils\DyeColor;
use pocketmine\block\VanillaBlocks;
use UnknowL\lib\forms\CustomForm;
use UnknowL\lib\forms\CustomFormResponse;
use UnknowL\lib\forms\element\Dropdown;
use UnknowL\lib\forms\element\Input;
use UnknowL\lib\inventoryapi\inventories\SimpleChestInventory;
use UnknowL\lib\inventoryapi\InventoryAPI;
use UnknowL\Linesia;
use UnknowL\player\LinesiaPlayer;
use UnknowL\task\InventoryAnimationTask;

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
			$color = !$i ? VanillaBlocks::CONCRETE()->setColor(DyeColor::GREEN()) : match ($i % 2)
			{
				0 => VanillaBlocks::CONCRETE()->setColor(DyeColor::BLACK()),
				default => VanillaBlocks::CONCRETE()->setColor(DyeColor::RED())
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
				"Rouge" => VanillaBlocks::CONCRETE()->setColor(DyeColor::RED())->asItem(),
				'Noir' => VanillaBlocks::CONCRETE()->setColor(DyeColor::BLACK())->asItem(),
				'Vert' => VanillaBlocks::CONCRETE()->setColor(DyeColor::GREEN())->asItem()
			};
			if(!is_int((int)$input->getValue())) return;
			Linesia::getInstance()->getScheduler()->scheduleRepeatingTask(new class($roulette, $inventory, $player, $block, (int)$input->getValue()) extends InventoryAnimationTask
			{

				public function __construct(array $items, SimpleChestInventory $inventory, LinesiaPlayer $player, private Concrete $misedColor, private int $mise = 1)
				{
					$this->items = $items;
					$this->inventory = $inventory;
					$this->player = $player;
					parent::__construct($items, $inventory, $player);
				}

				public function onCancel(): void
				{
					/**@var Concrete $final*/
					$final = $this->getResult();
					if ($this->player->isConnected())
					{
						if($final->getColor()->name() === $this->misedColor->getColor()->name())
						{
							$gain = match ($final)
							{
								VanillaBlocks::CONCRETE()->setColor(DyeColor::GREEN())->asItem() => $this->mise * 14,
								default => $this->mise * 2
							};
							$this->player->sendMessage("Vous avez gagné ". $gain);
							$this->inventory->onClose($this->player);
							return;
						}
						$this->player->sendMessage("Vous n'avez rien gagner");
						$this->inventory->onClose($this->player);
						parent::onCancel();
					}
				}
			}, 5);
		});
		$player->sendForm($form);
	}

	public function win(LinesiaPlayer $player, int $gain): void {/*empty*/}

	public function loose(LinesiaPlayer $player): void {/*empty*/}
}