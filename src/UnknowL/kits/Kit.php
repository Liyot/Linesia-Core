<?php

namespace UnknowL\kits;

use Cassandra\Date;
use DateTime;
use pocketmine\event\player\PlayerInteractEvent;
use pocketmine\event\player\PlayerItemUseEvent;
use pocketmine\item\Item;
use pocketmine\item\ItemFactory;
use pocketmine\item\VanillaItems;
use UnknowL\handlers\CooldownHandler;
use UnknowL\handlers\dataTypes\PlayerCooldown;
use UnknowL\lib\inventoryapi\inventories\SimpleChestInventory;
use UnknowL\lib\inventoryapi\InventoryAPI;
use UnknowL\Linesia;
use UnknowL\player\LinesiaPlayer;
use UnknowL\rank\Rank;
use UnknowL\handlers\dataTypes\Cooldown;
use UnknowL\utils\PathLoader;

final class Kit
{

	/**
	 * @param string $name
	 * @param string[] $rank
	 * @param Item[] $content
	 * @param array $contentDisplay
	 * @param array $armorContent
	 * @param array $armorDisplay
	 * @param string $cooldownData
	 */
	public function __construct(protected string $name, private array $rank, private array $content = [], private array $contentDisplay = [], private array $armorContent = [], private array $armorDisplay = [], private string $cooldownData = "")
	{
	}

	final public function canClaim(LinesiaPlayer $player): bool
	{
		$return = true;
		if(!count(array_filter($this->rank,fn($perm) => $player->hasPermission($perm))) >= 1 )
		{
			$player->sendMessage("Vous n'avez pas la permission");
			$return = false;
		}
		if(!$this->testCooldown($player))
		{
			$return =  false;
		}
		return $return;
	}

	private function testCooldown(LinesiaPlayer $player): bool
	{
		$cooldown = $player->getCooldown(PathLoader::PATH_KIT_COOLDOWN, strtolower($this->getName()));
		if(!is_null($cooldown))
		{
			if(!$cooldown->end())
			{
				$player->sendPopup(sprintf("Il te reste %s à attendre", $cooldown->format()));
				return false;
			}
		}

		return true;
	}

	final public function send(LinesiaPlayer $player): void
	{
		$data = sprintf("kit.%s.cooldown", strtolower($this->name));
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
			new PlayerCooldown(DateTime::createFromFormat("d:H:i:s", $this->cooldownData), $player, $data, (int)explode(":", $this->cooldownData)[0] === 0);
			$player->sendPopup(sprintf("[+] %s", $this->getName()));
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