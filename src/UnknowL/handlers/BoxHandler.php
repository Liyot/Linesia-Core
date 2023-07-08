<?php

namespace UnknowL\handlers;

use pocketmine\block\VanillaBlocks;
use pocketmine\item\Item;
use pocketmine\nbt\LittleEndianNbtSerializer;
use pocketmine\Server;
use pocketmine\utils\Config;
use pocketmine\world\Position;
use UnknowL\block\tiles\BoxTile;
use UnknowL\handlers\dataTypes\Box;
use UnknowL\Linesia;
use UnknowL\player\LinesiaPlayer;

class BoxHandler extends Handler
{

	public function __construct()
	{
		$this->loadData();
		parent::__construct();
	}

	/**
	 * @var $boxs Box[]
	 */
	private array $boxs = [];

	final public function addBox(Box $box)
	{
		$this->boxs[$box->getName()] ??= $box;
	}

    protected function loadData(): void
    {
		$config = new Config(Linesia::getInstance()->getDataFolder() . 'data/box/BoxTileData.json');
		foreach ($config->getAll() as $name => $data)
		{
			$this->loadBox($data["name"], $data["content"], $data["position"]);
		}
	}

    protected function saveData(): void
    {
		/*$config = new Config(Linesia::getInstance()->getDataFolder() . 'data/box/BoxTileData.json');
		foreach ($this->boxs as $name => $box)
		{
			$config->set($name, $box->serialize());
			var_dump("ee");
			$config->save();
		}
		var_dump($config);*/
	}

		final public function saveBox(Box $box)
		{
			$config = new Config(Linesia::getInstance()->getDataFolder(). 'data/box/BoxTileData.json');
			$config->set($box->getName(), $box->serialize());
			$config->save();
		}

	private function loadBox(string $name, array $itemData, array $posData)
	{
		$items = array_map(fn(array $tag) => Item::nbtDeserialize((new LittleEndianNbtSerializer())->read($tag['item'])->mustGetCompoundTag()), $itemData);
		$pos = new Position($posData[0], $posData[1], $posData[2], ($world = Server::getInstance()->getWorldManager()->getWorldByName($posData[3])));

		$box = new Box($name, $items);
		$box->setPosition($pos);
		$this->boxs[$name] = $box;

		$world->setBlock($pos, VanillaBlocks::AIR());
		$world->setBlock($pos, VanillaBlocks::CHEST());
		$world->getTile($pos)->getCleanedNBT()->setString('box', $name);
	}

	final public function getBox(string $name): ?Box
	{
		return $this->boxs[$name] ?? null;
	}

	final public function testPosition(Position $position, LinesiaPlayer $player): bool
	{
		$return = false;
		foreach ($this->boxs as $box)
        {
            if ($box->getPosition() !== null)
            {
				if ($box->getPosition()->equals($position))
				{
					$return = true;
					$box->open($player);
				}
            }
        }
        return $return;
	}

    public function getName(): string
    {
		return "Box";
	}
}