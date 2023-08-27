<?php

namespace UnknowL\items;

use pocketmine\item\ToolTier;
use pocketmine\utils\EnumTrait;

/**
 * This doc-block is generated automatically, do not modify it manually.
 * This must be regenerated whenever registry members are added, removed or changed.
 * @see build/generate-registry-annotations.php
 * @generate-registry-docblock
 *
 * @method static CustomToolTier RUBIS()
 */

final class CustomToolTier
{
	use EnumTrait {
		__construct as Enum___construct;
	}
	private ToolTier $tier;

	protected static function setup() : void{
		self::registerAll(
			new self("rubis", 5, 2000, 5, 4),
		);
	}

	private function __construct(
		string $name,
		private int $harvestLevel,
		private int $maxDurability,
		private int $baseAttackPoints,
		private int $baseEfficiency
	){
		$class = new \ReflectionClass(ToolTier::class);
		$constructor = $class->getConstructor();
		$constructor->setAccessible(true);
		$object = $class->newInstanceWithoutConstructor();
		$constructor->invoke($object, $name, $harvestLevel, $maxDurability, $baseAttackPoints, $baseEfficiency);

		$this->tier = $object;
		$this->Enum___construct($name);
	}

	/**
	 * @return ToolTier
	 */
	final public function getTier(): ToolTier
	{
		return $this->tier;
	}
}