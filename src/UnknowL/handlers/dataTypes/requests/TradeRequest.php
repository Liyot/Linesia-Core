<?php

namespace UnknowL\handlers\dataTypes\requests;

use pocketmine\block\utils\DyeColor;
use pocketmine\block\VanillaBlocks;
use pocketmine\item\Item;
use pocketmine\item\ItemBlock;
use UnknowL\lib\inventoryapi\inventories\BaseInventoryCustom;
use UnknowL\lib\inventoryapi\inventories\SimpleChestInventory;
use UnknowL\player\LinesiaPlayer;
use UnknowL\trait\InventoryContainerTrait;

final class TradeRequest extends Request
{

    use InventoryContainerTrait;

    const STATE_WAITING = 0, STATE_ONE_READY = 1, STATE_TWO_READY = 2;

    /**
     * @var SimpleChestInventory[]
     */
    protected array $sharedInventories = [];

    private array $limit = [];
    private int $states;


    public function accept(): void
    {
        $this->states = self::STATE_WAITING;
        $inventory = $this->getInventory();
        $inventory->setViewOnly(false);
        $inventory->setClickListener
        (
            function (LinesiaPlayer $player, BaseInventoryCustom $inventory, Item $sourceItem, Item $targetItem, int $slot)
            {
                if ($this->canInteract($slot, $player))
                {
                   $this->interact($player);
                   return;
                }
                $player->sendToastNotification("Impossible!", "Vous ne pouvez pas interagir avec cet élément");
            });

        $player = $this->getFrom();
        $this->sharedInventories[$player->getXuid()] = clone $inventory;
        $this->limit[$player->getXuid()] = [0 => 3, 9 => 12, 18 => 21, 27 => 30, 36 => 39, 45 => 48];
        $player->sendMessage("Pensez a garder des places dans votre inventaire!");

        $player = $this->getTo();
        $this->sharedInventories[$player->getXuid()] = clone $inventory;
        $this->limit[$player->getXuid()] = [5 => 8, 14 => 17, 23 => 26, 32 => 35, 41 => 44, 50 => 53];

    }

    public function getName(): string
    {
        return "Trade";
    }

    public function getChatFormat(): string
    {
        return $this->getName();
    }

    private function getLimit(LinesiaPlayer $player): array
    {
        return $this->limit[$player->getXuid()];
    }

    private function canInteract(int $slot, LinesiaPlayer $actor): bool
    {
        foreach ($this->getLimit($actor) as $min => $max)
        {
            if ($slot < $min || $slot > $max) return false;
            if ($slot === 53 || $slot === 45) $this->confirm($actor);
        }
        return true;
    }

    public function interact(LinesiaPlayer $player): void
    {
        $target = $this->getOppositePlayer($player);
        $this->sharedInventories[$target->getXuid()]->setContents($this->sharedInventories[$player->getXuid()]->getContents());
    }

    public function getOppositePlayer(LinesiaPlayer $player): LinesiaPlayer
    {
        return $this->getFrom()->getXuid() === $player->getXuid() ? $this->getTo() : $this->getFrom();
    }

    final public function confirm(LinesiaPlayer $player): void
    {
        if ($this->states === self::STATE_TWO_READY)
        if($this->states === self::STATE_ONE_READY)
        {
            $this->states = self::STATE_TWO_READY;
            $this->trade($player);

            return;
        }
        $this->states = self::STATE_ONE_READY;
    }

    final public function trade(LinesiaPlayer $player): void
    {
        $target = $this->getOppositePlayer($player);
        $parsed = false;
        foreach ([$this->getLimit($player), $this->getLimit($target)] as $min => $max)
        {
            /**
             * @var ItemBlock|Item $item
             */
            foreach ($this->getInterval($min, $max) as $item)
            {
                $parsed ? $target->getInventory()->addItem($item) : $player->getInventory()->addItem($item);
                $parsed = true;
            }
        }
    }

    public function getContents(): array
    {
        $block = VanillaBlocks::GLASS_PANE()->asItem();
        $confirmBlock = VanillaBlocks::CONCRETE()->setColor(DyeColor::GREEN())->asItem();
        return [ 4 => $block, 13 => $block,  22 => $block, 31 => $block, 40 => $block, 49 => $block, 45 => $confirmBlock, 53 => $confirmBlock];
    }
}