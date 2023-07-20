<?php

namespace UnknowL\handlers;

use pocketmine\block\VanillaBlocks;
use pocketmine\item\Item;
use pocketmine\math\AxisAlignedBB;
use pocketmine\nbt\LittleEndianNbtSerializer;
use pocketmine\Server;
use pocketmine\utils\Config;
use pocketmine\world\Position;
use UnknowL\entities\FloatingText;
use UnknowL\handlers\dataTypes\Box;
use UnknowL\Linesia;
use UnknowL\player\LinesiaPlayer;

class BoxHandler extends Handler
{

	public function __construct()
	{
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

	final public function removeBox(Box $box)
	{
		if (isset($this->boxs[$box->getName()]))
		{
			if (!is_null($box->getPosition()))
			{
				$world = $box->getPosition()->getWorld();
				$entity = $world->getNearestEntity($box->getPosition()->add(0.5, 1, 0.5), 1);
				if ($entity instanceof FloatingText)
				{
					$entity->flagForDespawn();
				}
				unset($this->boxs[$box->getName()]);
			}
		}
	}

    protected function loadData(): void
    {
		$config = new Config(Linesia::getInstance()->getDataFolder() . 'data/box/BoxTileData.json');
		foreach ($config->getAll() as $name => $data)
		{
			$this->loadBox($data["name"], $data["content"], $data["position"], $data["key"]);
		}
	}

    protected function saveData(): void
    {

	}

		final public function saveBox(Box $box)
		{
			$config = new Config(Linesia::getInstance()->getDataFolder(). 'data/box/BoxTileData.json');
			$config->set($box->getName(), $box->serialize());
			$config->save();
		}

	private function loadBox(string $name, array $itemData, array $posData, string $keyBuffer)
	{
		$items = array_map(fn(array $tag) => Item::nbtDeserialize((new LittleEndianNbtSerializer())->read($tag['item'])->mustGetCompoundTag()), $itemData);
		$pos = new Position($posData[0], $posData[1], $posData[2], ($world = Server::getInstance()->getWorldManager()->getWorldByName($posData[3])));

		$box = new Box($name, $items);
		$box->setPosition($pos);

		$buffer = Item::nbtDeserialize((new LittleEndianNbtSerializer())->read($keyBuffer)->mustGetCompoundTag());
		$box->setKey($buffer);
		$this->boxs[$name] = $box;

		$world->setBlock($pos, VanillaBlocks::AIR());
		$world->setBlock($pos, VanillaBlocks::CHEST());
		$world->getTile($pos)->getCleanedNBT()->setString('box', $name);
	}

	final public function getBox(string $name): ?Box
	{
		return $this->boxs[$name] ?? null;
	}

	final public function getBoxByPosition(Position $pos):?Box
	{
		foreach ($this->boxs as $box)
        {
            if ($box->getPosition()->equals($pos))
            {
                return $box;
            }
        }
        return null;
	}


	final public function testPosition(Position $position, ?LinesiaPlayer $player = null): bool
	{
		$return = false;
		foreach ($this->boxs as $box)
        {
            if ($box->getPosition() !== null)
            {
				if ($box->getPosition()->equals($position))
				{
					$return = true;
					if (!is_null($player))
					{
						$box->open($player);
					}
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