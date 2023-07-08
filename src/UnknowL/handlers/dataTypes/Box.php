<?php

namespace UnknowL\handlers\dataTypes;

use pocketmine\block\VanillaBlocks;
use pocketmine\data\bedrock\block\upgrade\BlockDataUpgrader;
use pocketmine\data\bedrock\block\upgrade\LegacyBlockIdToStringIdMap;
use pocketmine\data\bedrock\item\SavedItemData;
use pocketmine\data\bedrock\item\upgrade\LegacyItemIdToStringIdMap;
use pocketmine\entity\Location;
use pocketmine\item\Item;
use pocketmine\math\AxisAlignedBB;
use pocketmine\nbt\LittleEndianNbtSerializer;
use pocketmine\nbt\TreeRoot;
use pocketmine\world\format\io\GlobalBlockStateHandlers;
use pocketmine\world\format\io\GlobalItemDataHandlers;
use pocketmine\world\Position;
use UnknowL\entities\FloatingText;
use UnknowL\handlers\Handler;
use UnknowL\lib\forms\menu\Button;
use UnknowL\lib\forms\MenuForm;
use UnknowL\lib\inventoryapi\inventories\SimpleChestInventory;
use UnknowL\lib\inventoryapi\InventoryAPI;
use UnknowL\Linesia;
use UnknowL\player\LinesiaPlayer;
use UnknowL\task\InventoryAnimationTask;
use UnknowL\trait\InventoryContainerTrait;

final class Box
{
	use InventoryContainerTrait;

	private string $worldName;

	/**
	 * @param string $name
	 * @param Item[] $items
	 * @param Position|null $position
	 */
	public function __construct(private string $name, private array $items, private ?Position $position = null)
	{
		array_map(fn(Item $item) => $item->setLore([sprintf("(%d)", $item->getNamedTag()->getInt('percentage'))]), $this->items);
	}

	public function open(LinesiaPlayer $player): void
	{
		$form = MenuForm::withOptions('Choississez votre option:', '', ['Obtenir', 'Prévisualisez'], function (LinesiaPlayer $player, Button $selected)
		{
			($selected->text === 'Obtenir') ?  $this->startAnimation($player, InventoryAPI::createSimpleChest(true)) : $this->previsualize($player);
		});
		$player->sendForm($form);
	}

	public function previsualize(LinesiaPlayer $player): void
	{
		$inventory = InventoryAPI::createDoubleChest(true);
		$inventory->setContents($this->getItems());
		$inventory->send($player);
	}

	final public function serialize(): array
	{
		$pos = $this->getPosition();
		return
			[
				"name" => $this->getName(),
				"content" => $this->jsonSerialiezeItems(),
				"position" => [$pos->getX(), $pos->getY(), $pos->getZ(), $this->worldName]
			];
	}

	final protected function jsonSerialiezeItems(): array
	{

		return array_map(fn(Item $item, int $slot) =>
		[
			"item" => (new LittleEndianNbtSerializer())->write(new TreeRoot($item->nbtSerialize($slot)))
		], $this->items, array_keys($this->items));
	}

	final public function asItem(): Item
	{
		$item = VanillaBlocks::CHEST()->asItem();
		$item->getNamedTag()->setString('Box', $this->name);
		return $item;
	}

	final public function place(Position $position, LinesiaPlayer $player): void
	{
		$this->setPosition($position);
		$player->sendMessage('Vous avez créer avec succés la box ' . $this->getName());
	}

	public function sortByPercentage(): array
	{
		$array = [];
		foreach ($this->items as $item)
		{
			$multuplier = round((100 / $this->getPercentageFromLore($item->getLore()[0]) * count($this->items)));
			for ($i = $multuplier; $i > 0; $i--) $array[] = $item;
			var_dump($multuplier);
		}
		shuffle($array);
		return $array;
	}

	private function getPercentageFromLore(string $name): int
	{
		return (int)preg_replace('/[^0-9]/', '', $name);
	}

	protected function startAnimation(LinesiaPlayer $player, SimpleChestInventory $inventory): void
	{
		$inventory->send($player);
		Linesia::getInstance()->getScheduler()->scheduleRepeatingTask(new class($this->sortByPercentage(), $inventory, $player) extends InventoryAnimationTask
		{
			public function onCancel(): void
			{
				parent::onCancel();
				$this->player->getInventory()->addItem($this->getResult());
			}
		}, 5);
	}

	final public function getPosition(): ?Position
	{
		return $this->position ?? null;
	}

	public function isPlaced(): bool
	{
		return is_null($this->position);
	}

	final public function setPosition(Position $position): void
	{
		$this->position = $position;
		$this->worldName = $position->getWorld()->getDisplayName();
		$pos = $position->floor();
		$entity = new FloatingText(Location::fromObject($pos->add(0.5, 1, 0.5), $position->getWorld()));
		$entity->setText($this->getName());
		$entity->spawnToAll();
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
	public function getItems(): array
	{
		return $this->items;
	}

	public function getContents(): array
	{
		return $this->items;
	}

	public function __destruct()
	{
		Handler::BOX()->saveBox($this);
	}
}