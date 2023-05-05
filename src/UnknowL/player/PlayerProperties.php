<?php

use pocketmine\nbt\tag\CompoundTag;
use pocketmine\nbt\tag\ListTag;
use UnknowL\player\LinesiaPlayer;
use UnknowL\trait\PropertiesTrait;

final class PlayerProperties
{
	use PropertiesTrait;

	protected array $array = [];


	public function __construct(private LinesiaPlayer $player)
	{
		if(!($nbt = $this->player->saveNBT())->getCompoundTag('properties') || empty($nbt->getCompoundTag("properties")->getValue())){
			$this->setBaseProperties([

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
			};
		}
		return $nbt;
	}
}