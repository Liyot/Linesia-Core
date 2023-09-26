<?php

namespace UnknowL\player;

use pocketmine\nbt\tag\CompoundTag;
use pocketmine\nbt\tag\IntegerishTagTrait;
use pocketmine\nbt\tag\IntTag;
use pocketmine\nbt\tag\ListTag;
use pocketmine\nbt\tag\StringTag;
use pocketmine\Server;
use UnknowL\handlers\Handler;
use UnknowL\Linesia;
use UnknowL\trait\PropertiesTrait;
use UnknowL\utils\PathLoader;

final class PlayerProperties
{
	use PropertiesTrait;

	public function __construct(CompoundTag $nbt)
	{
		if($nbt->getCompoundTag('properties') === null || empty($nbt->getCompoundTag("properties")->getValue()))
		{
			$this->setBaseProperties([
				"rank" => Handler::RANK()->getDefaultRank()->getName(),
                PathLoader::PATH_RANK_ADD_PERM => [
					"cooldown" => null
				],
                PathLoader::PATH_RANK_CACHE => [
					"cooldown" => null
					],
				"activeTag" => [],
				"purchasedTags" => [],
				"permissions" => [
					"temp" => [

					],
					"normal" => [

					]
				],
				"basekit" => [
					"cooldown" => null
				],
				"manager" => [
						"statistics" => [
							'death' => 0,
							'kill' => 0,
							'kd' => 0.0,
							'gametime' => 0,
							'blockmined' => 0,
							'blockposed' => 0,
							'firstconnexion' => 0,
							'lastconnexion' => 0,
							'dualwon' => 0
						],
					],
				"keys" => [
					"firstkey" => 0,
					"secondkey" => 0
					]
			]);
		}else{
			$this->setBaseProperties($this->TagtoArray($nbt->getCompoundTag("properties")));
		}
	}

	public function save(CompoundTag $tag): void
	{
		$tag->setTag("properties", $this->arraytoTag($this->getPropertiesList()));
	}

	private function TagtoArray(CompoundTag|ListTag $nbt, $name = null): array{
		foreach($nbt->getValue() as $key => $value){
			if($value instanceof CompoundTag || $value instanceof ListTag){
				self::TagtoArray($value, array_search($value, $nbt->getValue(), true));
			}else{
				$name === null ? $this->properties[$key] = $value->getValue() : $this->properties[$name][$key] = $value->getValue();
			}
		}
		return $this->properties;
	}

	private function arraytoTag(array $array, ?CompoundTag $nbt = null, string $name = ""): CompoundTag {
		$nbt ??= new CompoundTag();
		foreach($array as $property => $value){
			match (gettype($value)){
				"integer" => $value > 0x7fffffff ? $nbt->setLong($property, $value) : $nbt->setInt($property, $value),
				"double" => $nbt->setDouble($property, $value),
				"string" => $nbt->setString($property, $value),
				"boolean" => $nbt->setByte($property, $value),
				"array" =>  $nbt->setTag($property, self::arrayToTag($value, null)),
				"object" => $nbt->setString($property, $value->serialize()),
				"NULL" =>  $nbt->setString($property, "'null'")
			};
		}
		return $nbt;
	}
}