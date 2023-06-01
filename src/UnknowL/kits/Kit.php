<?php

namespace UnknowL\kits;

use pocketmine\item\Item;
use pocketmine\item\ItemFactory;
use pocketmine\item\VanillaItems;
use UnknowL\lib\inventoryapi\inventories\SimpleChestInventory;
use UnknowL\lib\inventoryapi\InventoryAPI;
use UnknowL\Linesia;
use UnknowL\player\LinesiaPlayer;
use UnknowL\rank\Rank;
use UnknowL\utils\Cooldown;

final class Kit
{

	/**
	 * @param string $name
	 * @param string[] $rank
	 * @param Item[] $content
	 * @param array $contentDisplay
	 * @param array $armorContent
	 * @param array $armorDisplay
	 * @param array $cooldownData
	 */
	public function __construct(protected string $name, private array $rank, private array $content = [], private array $contentDisplay = [], private array $armorContent = [], private array $armorDisplay = [], private array $cooldownData = [])
	{
	}

	final public function canClaim(LinesiaPlayer $player): bool
	{
		return
			(count(array_filter($this->rank,fn($perm) => $player->getRank()->testPermission($perm))) >= 1)
			&& $this->testCooldown($player);

	}

	private function testCooldown(LinesiaPlayer $player): bool
	{
		$cooldown = Linesia::getInstance()->getCooldownHandler()->unserizalize($player->getPlayerProperties()->getNestedProperties("kit.%s.cooldown"));
		if(!$cooldown->end())
		{
			$player->sendPopup(sprintf("Il te reste %s à attendre", $cooldown->format()));
			return true;
		}
		return false;
	}

	final public function send(LinesiaPlayer $player): void
	{
		if($this->canClaim($player))
		{
			$func = function ($value) : Item
			{
				$ex = explode(":", $value);
				return ItemFactory::getInstance()->get($ex[0], $ex[1]);
			};

			array_map(function($item) use ($func, $player) {
				if ($player->getArmorInventory()->canAddItem($func($item)))
				{
					$player->getArmorInventory()->addItem($func($item));
				}
			} , $this->armorContent);

			array_map(fn($item) => $player->getInventory()->addItem($func($item)), $this->content);
			$cooldown = new Cooldown(fn(LinesiaPlayer $player) => $player->getPlayerProperties()->setNestedProperties(sprintf("kit.%s.cooldown", strtolower($this->name)), null),
				$this->cooldownData[0], $this->cooldownData[1], $this->cooldownData[2], $this->cooldownData[3], sprintf("kit.%s.cooldown", strtolower($this->name)), $player);

			$player->getPlayerProperties()->setNestedProperties(sprintf("kit.%s.cooldown", strtolower($this->name)), $cooldown);
		}
	}

	final public function previsualize(): SimpleChestInventory
	{
		$form = InventoryAPI::createDoubleChest(true);
		//$armorIndex =
		for ($i = 0 ; $i < count($this->armorDisplay) ; $i++)
		{
			$ex = explode(":", $this->armorContent[$i]);
			$item = ItemFactory::getInstance()->get($ex[0], $ex[1]);
			$form->setItem($this->armorDisplay[$i], $item);
		}

		for ($i = 0 ; $i < count($this->contentDisplay) ; $i++)
		{
			$ex = explode(":", $this->content[$i]);
			$item = ItemFactory::getInstance()->get($ex[0], $ex[1]);
			$form->setItem($this->contentDisplay[$i], $item);
		}

		return $form;
	}

	final public function getCooldown(): Cooldown
	{
		return $this->cooldown;
	}


	/**
	 * @return array
	 */
	public function getContent(): array
	{
		return $this->content;
	}

	/**
	 * @return Rank[]
	 */
	public function getRank(): array
	{
		return $this->rank;
	}

	/**
	 * @return string
	 */
	public function getName(): string
	{
		return $this->name;
	}

	/**
	 * @return array
	 */
	public function getArmorContent(): array
	{
		return $this->armorContent;
	}

	/**
	 * @return array
	 */
	public function getContentDisplay(): array
	{
		return $this->contentDisplay;
	}

	/**
	 * @return array
	 */
	public function getArmorDisplay(): array
	{
		return $this->armorDisplay;
	}
}