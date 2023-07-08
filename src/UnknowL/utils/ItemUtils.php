<?php

use pocketmine\data\bedrock\block\upgrade\LegacyBlockIdToStringIdMap;
use pocketmine\data\bedrock\item\SavedItemData;
use pocketmine\data\bedrock\item\upgrade\LegacyItemIdToStringIdMap;
use pocketmine\world\format\io\GlobalBlockStateHandlers;
use pocketmine\world\format\io\GlobalItemDataHandlers;

abstract class ItemUtils
{
	public static function getLegacyMeta(string $itemStringId) : int
	{
		$metaUpgrader = new \ReflectionProperty(GlobalItemDataHandlers::getDeserializer(), 'mappingTable');
		$metaUpgrader->setAccessible(true);

		foreach ($metaUpgrader->getValue(GlobalBlockStateHandlers::getUpgrader()->getBlockIdMetaUpgrader()) as $key => $value)
		{
			var_dump([$key, $value]);
		}

		return 0;
	}

	public static function getLegacyId(int $itemTypeId) : ?int
	{
		$deserializers = new \ReflectionProperty(GlobalItemDataHandlers::getDeserializer(), 'deserializers');
		$deserializers->setAccessible(true);
		foreach ($deserializers->getValue(GlobalItemDataHandlers::getDeserializer()) as $identifier => $deserializer)
		{
			$item = $deserializer(new SavedItemData($identifier));
			if($item->getTypeId() === $itemTypeId)
			{
				foreach (LegacyItemIdToStringIdMap::getInstance()->getLegacyToStringMap() as $legacyId => $stringId)
				{
					if ($stringId === $identifier) return $legacyId;
				}

				foreach (LegacyBlockIdToStringIdMap::getInstance()->getLegacyToStringMap() as $legacyBlockId => $stringId)
				{
					if ($stringId === $identifier) return $legacyBlockId;
				}
			}
		}
		return null;
	}

	public static function getStringId(int $itemTypeId) :?string
	{
		$deserializers = new \ReflectionProperty(GlobalItemDataHandlers::getDeserializer(), 'deserializers');
		$deserializers->setAccessible(true);
		foreach ($deserializers->getValue(GlobalItemDataHandlers::getDeserializer()) as $identifier => $deserializer)
		{
			$item = $deserializer(new SavedItemData($identifier));
			if($item->getTypeId() === $itemTypeId)
			{
				foreach (LegacyItemIdToStringIdMap::getInstance()->getLegacyToStringMap() as $legacyId => $stringId)
				{
					if ($stringId === $identifier) return $stringId;
				}

				foreach (LegacyBlockIdToStringIdMap::getInstance()->getLegacyToStringMap() as $legacyBlockId => $stringId)
				{
					if ($stringId === $identifier) return $stringId;
				}
			}
		}
		return null;
	}
}