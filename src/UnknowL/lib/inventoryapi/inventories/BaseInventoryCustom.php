<?php

namespace UnknowL\lib\inventoryapi\inventories;

use pocketmine\block\inventory\BlockInventory;
use pocketmine\block\inventory\BlockInventoryTrait;
use pocketmine\inventory\SimpleInventory;
use pocketmine\network\mcpe\convert\TypeConverter;
use pocketmine\network\mcpe\protocol\types\BlockPosition;
use pocketmine\network\mcpe\protocol\UpdateBlockPacket;
use pocketmine\player\Player;

abstract class BaseInventoryCustom extends SimpleInventory implements BlockInventory
{
    use BlockInventoryTrait;
    protected string $name = "Chest";
    protected bool $hasViewOnly = false;
    protected $clickListener = null;
	protected $previousClickListener = null;
    protected $closeListener = null;
    private bool $transactionCancel = false;


    public function __construct(int $size = 27) {
        parent::__construct($size);
    }

    public function getName() : string{
        return $this->name;
    }

    public function transactionCancel(): self {
        $this->transactionCancel = true;
        return $this;
    }

    public function isCancelTransaction(): bool {
        return $this->transactionCancel;
    }

    public function reloadTransaction(): self {
        $this->transactionCancel = false;
        return $this;
    }

    public function setName(string $value): self {
        $this->name = $value;
        return $this;
    }

    public function setViewOnly(bool $value = true): self {
        $this->hasViewOnly = $value;
        return $this;
    }
        
    public function isViewOnly() : bool{
        return $this->hasViewOnly;
    }

    public function getClickListener(){
        return $this->clickListener;
    }

    public function setClickListener(?callable $callable): self {
		if (!is_null($this->clickListener)) $this->previousClickListener = $this->clickListener;
        $this->clickListener = $callable;
        return $this;
    }

	public function rewindClickListener(): void
	{
		if (is_null($this->previousClickListener)) return;
		$this->clickListener = $this->previousClickListener;
        $this->previousClickListener = null;
	}

    public function getCloseListener(){
        return $this->closeListener;
    }

    public function setCloseListener(?callable $callable): self {
        $this->closeListener = $callable;
        return $this;
    }

    public function onClose(Player $who) : void {
        parent::onClose($who);

        $who->getNetworkSession()->sendDataPacket(UpdateBlockPacket::create
		(
			BlockPosition::fromVector3($this->holder),
			TypeConverter::getInstance()->getBlockTranslator()->internalIdToNetworkId($who->getWorld()->getBlock($this->holder)->getStateId()),
			UpdateBlockPacket::FLAG_NETWORK,
			UpdateBlockPacket::DATA_LAYER_NORMAL)
		);

        $closeListener = $this->getCloseListener();
        if ($closeListener !== null){
            $closeListener($who, $this);
        }
    }
}
