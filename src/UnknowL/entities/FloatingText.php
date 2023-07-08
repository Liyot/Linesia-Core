<?php

namespace UnknowL\entities;

use pocketmine\block\VanillaBlocks;
use pocketmine\entity\Entity;
use pocketmine\entity\EntitySizeInfo;
use pocketmine\entity\Location;
use pocketmine\nbt\tag\CompoundTag;
use pocketmine\network\mcpe\convert\TypeConverter;
use pocketmine\network\mcpe\protocol\types\entity\EntityIds;
use pocketmine\network\mcpe\protocol\types\entity\EntityMetadataCollection;
use pocketmine\network\mcpe\protocol\types\entity\EntityMetadataFlags;
use pocketmine\network\mcpe\protocol\types\entity\EntityMetadataProperties;

class FloatingText extends Entity{
	private string $text = "";

	public float $gravity = 0.0;
	public $canCollide = false;
	public bool $keepMovement = true;
	protected bool $gravityEnabled = false;
	protected float $drag = 0.0;
	protected float $scale = 0.0;
	protected $immobile = true;

	public function __construct(Location $location, ?CompoundTag $nbt = null )
	{
		$this->setNameTagAlwaysVisible();
		$this->canCollide = false;
		parent::__construct($location, $nbt);
	}

	protected function getInitialSizeInfo(): EntitySizeInfo
	{
		return new EntitySizeInfo(0.0, 0.0);
	}

	protected function syncNetworkData(EntityMetadataCollection $properties) : void{
		$properties->setByte(EntityMetadataProperties::ALWAYS_SHOW_NAMETAG, $this->alwaysShowNameTag ? 1 : 0);
		$properties->setFloat(EntityMetadataProperties::SCALE, $this->scale);
		$properties->setFloat(EntityMetadataProperties::BOUNDING_BOX_HEIGHT, 0);
		$properties->setFloat(EntityMetadataProperties::BOUNDING_BOX_WIDTH, 0);
		$properties->setString(EntityMetadataProperties::NAMETAG, $this->nameTag);
		$properties->setLong(EntityMetadataProperties::LEAD_HOLDER_EID, -1);
		$properties->setLong(EntityMetadataProperties::OWNER_EID, $this->ownerId ?? -1);
		$properties->setLong(EntityMetadataProperties::TARGET_EID, $this->targetId ?? 0);
		$properties->setGenericFlag(EntityMetadataFlags::IMMOBILE, $this->immobile);
		$properties->setInt(EntityMetadataProperties::VARIANT, TypeConverter::getInstance()->getBlockTranslator()->internalIdToNetworkId(VanillaBlocks::AIR()->getStateId()));
		$properties->setString(EntityMetadataProperties::SCORE_TAG, $this->scoreTag);
		$properties->setByte(EntityMetadataProperties::COLOR, 0);
		$properties->setGenericFlag(EntityMetadataFlags::AFFECTED_BY_GRAVITY, $this->gravityEnabled);
		$properties->setGenericFlag(EntityMetadataFlags::CAN_CLIMB, $this->canClimb);
		$properties->setGenericFlag(EntityMetadataFlags::CAN_SHOW_NAMETAG, $this->nameTagVisible);
		$properties->setGenericFlag(EntityMetadataFlags::HAS_COLLISION, true);
		$properties->setGenericFlag(EntityMetadataFlags::IMMOBILE, $this->immobile);
		$properties->setGenericFlag(EntityMetadataFlags::INVISIBLE, $this->invisible);
		$properties->setGenericFlag(EntityMetadataFlags::SILENT, $this->silent);
		$properties->setGenericFlag(EntityMetadataFlags::ONFIRE, $this->isOnFire());
		$properties->setGenericFlag(EntityMetadataFlags::WALLCLIMBING, $this->canClimbWalls);
	}

	public static function getNetworkTypeId(): string
	{
		return EntityIds::FALLING_BLOCK;
	}

	public function setText(string $text): void
	{
		$this->text = $text;
		$this->setNameTag($text);
	}

	public function getText(): string{
		return $this->text;
	}

	protected function getInitialDragMultiplier(): float
	{
		return 0.0;
	}

	protected function getInitialGravity(): float
	{
		return 0.0;
	}
}