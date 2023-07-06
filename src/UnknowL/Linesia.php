<?php

namespace UnknowL;

use Closure;
use pocketmine\block\VanillaBlocks;
use pocketmine\data\bedrock\item\SavedItemData;
use pocketmine\data\bedrock\item\SavedItemStackData;
use pocketmine\data\bedrock\item\upgrade\LegacyItemIdToStringIdMap;
use pocketmine\item\StringToItemParser;
use pocketmine\item\VanillaItems;
use pocketmine\plugin\PluginBase;
use pocketmine\utils\SingletonTrait;
use pocketmine\world\format\io\GlobalBlockStateHandlers;
use pocketmine\world\format\io\GlobalItemDataHandlers;
use UnknowL\casino\types\Pyramides;
use UnknowL\handlers\dataTypes\Kit;
use UnknowL\trait\LoaderTrait;

final class Linesia extends PluginBase
{
	use SingletonTrait, LoaderTrait;

	public function onEnable(): void
	{
		self::setInstance($this);

		$findMeta = function (string $stringId) : ?int
		{
			$metaUpgrader = new \ReflectionProperty(GlobalItemDataHandlers::getDeserializer(), 'mappingTable');
			$metaUpgrader->setAccessible(true);
			foreach ($metaUpgrader->getValue(GlobalBlockStateHandlers::getUpgrader()->getBlockIdMetaUpgrader()) as $key => $value)
			{
				var_dump([$key, $value ]);
			}
			return 0;
		};
		$this->loadAll();
	}

	public function onDisable(): void
	{
		$this->saveAll();
	}
}