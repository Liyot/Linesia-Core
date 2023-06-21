<?php

namespace UnknowL\player;

use pocketmine\nbt\tag\CompoundTag;
use pocketmine\nbt\tag\ListTag;
use pocketmine\nbt\tag\StringTag;
use UnknowL\handlers\Handler;
use UnknowL\Linesia;
use UnknowL\trait\PropertiesTrait;
use UnknowL\utils\PathLoader;

final class PlayerProperties
{
	use PropertiesTrait;

	protected array $array = [];


	public function __construct(private LinesiaPlayer $player)
	{
		if(!($nbt = $this->player->saveNBT())->getCompoundTag('properties') || empty($nbt->getCompoundTag("properties")->getValue())){
			$this->setBaseProperties([
				"rank" => Handler::RANK()->getDefaultRank()->getName(),
                PathLoader::PATH_RANK_ADD_PERM => null,
                PathLoader::PATH_RANK_CACHE => null,
				"kit" => [
					"base" => [
						"cooldown" => null
					],
				],
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
				$name === null ? $this->array[$key] = $value->getValue() : $this->array[$name][$key] = $value->getValue();
			}
		}
		return $this->properties;
	}

	private function arraytoTag(array $array): CompoundTag {
		$nbt = new CompoundTag();
		foreach($array as $property => $value){
			match (gettype($value)){
				"integer" => $nbt->setInt($property, $value),
				"double" => $nbt->setDouble($property, $value),
				"string" => $nbt->setString($property, $value),
				"boolean" => $nbt->setByte($property, $value),
				"array" => $nbt->setTag($property, self::arrayToTag($value)),
				"object" => $nbt->setString($property, $value->serialize()),
				"NULL" =>  $nbt->setString($property, "'null'")
			};
		}
		return $nbt;
	}
}