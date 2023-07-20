<?php

namespace UnknowL\handlers\dataTypes;

use Cassandra\Date;
use DateTime;
use pocketmine\data\bedrock\EnchantmentIdMap;
use pocketmine\data\bedrock\item\upgrade\LegacyItemIdToStringIdMap;
use pocketmine\inventory\ArmorInventory;
use pocketmine\item\Armor;
use pocketmine\item\ArmorTypeInfo;
use pocketmine\item\enchantment\Enchantment;
use pocketmine\item\enchantment\EnchantmentInstance;
use pocketmine\item\Item;
use pocketmine\item\StringToItemParser;
use UnknowL\lib\inventoryapi\inventories\SimpleChestInventory;
use UnknowL\lib\inventoryapi\InventoryAPI;
use UnknowL\player\LinesiaPlayer;
use UnknowL\rank\Rank;
use UnknowL\utils\PathLoader;

final class Kit
{

	/**
	 * @param string $name
	 * @param string $permission
	 * @param Item[] $content
	 * @param array $contentDisplay
	 * @param array $armorContent
	 * @param array $armorDisplay
	 * @param string $cooldownData
	 * @param array $armorEnchantData
	 * @param array $contentEnchant
	 */
	public function __construct
	(
		protected string        $name,
		private readonly string $permission,
		private array           $content = [],
		private readonly array  $contentDisplay = [],
		private array           $armorContent = [],
		private readonly array  $armorDisplay = [],
		private readonly string $cooldownData = "",
		private array           $armorEnchantData = [],
		private array           $contentEnchant = []
	){}

	final public function canClaim(LinesiaPlayer $player): bool
	{
		$return = true;
		var_dump($this->getPermission());
		if(!$player->hasPermission($this->getPermission()))
		{
			$player->sendMessage("§cVous n'avez pas la permission");
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
		$path = sprintf(PathLoader::PATH_KIT_COOLDOWN, strtolower($this->getName()));
		$cooldown = $player->getCooldown($path);
		if(!is_null($cooldown))
		{
			if(!$cooldown->end())
			{
				$player->sendPopup(sprintf("§cIl te reste %s à attendre", $cooldown->format()));
				return false;
			}
		}
		return true;
	}

	final public function send(LinesiaPlayer $player): void
	{
		$data = sprintf(PathLoader::PATH_KIT_COOLDOWN, strtolower($this->name));
		if($this->canClaim($player))
		{
			$func = function ($value, array &$array, array &$enchant) : Item
			{
				$exp = explode(":", $value);
				$key = array_search($value, $array, true);
				$item = StringToItemParser::getInstance()->parse(LegacyItemIdToStringIdMap::getInstance()->legacyToString($exp[0]))->setCount($exp[2]);
				if (isset($enchant[$key]))
				{
					$ex = explode(":", $enchant[$key]);
					$item->addEnchantment(new EnchantmentInstance(EnchantmentIdMap::getInstance()->fromId($ex[0]), $ex[1]));
				}
				return $item;
			};

			array_map(function($item) use ($func, &$player) {
				/**@var Armor $item*/
				$item = $func($item, $this->armorContent, $this->armorEnchantData);
				if ($player->getArmorInventory()->canAddItem($item))
				{
					$player->getArmorInventory()->setItem($item->getArmorSlot(), $item);
					return;
				}
				$player->getInventory()->addItem($item);
			} , $this->armorContent);

			array_map(fn($item) => $player->getInventory()->addItem($func($item, $this->content, $this->contentEnchant)), $this->content);
			new PlayerCooldown((int) $this->cooldownData, $player, $data);
            $name = $this->getName();
			$player->sendMessage("§aVous avez récupéré le kit $name !");
		}
	}

	final public function previsualize(): SimpleChestInventory
	{
		$form = InventoryAPI::createDoubleChest(true);

		for ($i = 0 ; $i < count($this->armorDisplay) ; $i++)
		{
			$ex = explode(":", $this->armorContent[$i]);
			$item = StringToItemParser::getInstance()->parse(LegacyItemIdToStringIdMap::getInstance()->legacyToString($ex[0]));

			if (isset($this->armorEnchantData[$i]))
			{
				$enchant = explode(":", $this->armorEnchantData[$i]);
				$item->addEnchantment(new EnchantmentInstance(EnchantmentIdMap::getInstance()->fromId($enchant[0]), $enchant[1]));
			}

			$form->setItem($this->armorDisplay[$i], $item);
		}

		for ($i = 0 ; $i < count($this->contentDisplay); $i++)
		{
			$ex = explode(":", $this->content[$i]);
			$item = StringToItemParser::getInstance()->parse(LegacyItemIdToStringIdMap::getInstance()->legacyToString($ex[0]));

			if (isset($this->contentEnchant[$i]))
			{
				$enchant = explode(":", $this->contentEnchant[$i]);
				$item->addEnchantment(new EnchantmentInstance(EnchantmentIdMap::getInstance()->fromId($enchant[0]), $enchant[1]));
			}

			$form->setItem($this->contentDisplay[$i], $item);
		}
		return $form;
	}

	/**
	 * @return array
	 */
	public function getContent(): array
	{
		return $this->content;
	}

	/**
	 * @return string
	 */
	public function getPermission(): string
	{
		return $this->permission;
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