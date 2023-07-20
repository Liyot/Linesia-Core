<?php

namespace UnknowL\handlers\dataTypes;

use pocketmine\item\Item;
use pocketmine\nbt\LittleEndianNbtSerializer;
use pocketmine\nbt\tag\IntTag;
use pocketmine\nbt\TreeRoot;
use pocketmine\Server;
use UnknowL\handlers\Handler;
use UnknowL\handlers\ShopHandler;
use UnknowL\lib\forms\BaseForm;
use UnknowL\lib\forms\CustomForm;
use UnknowL\lib\forms\CustomFormResponse;
use UnknowL\lib\forms\element\Label;
use UnknowL\lib\forms\element\Slider;
use UnknowL\lib\forms\menu\Button;
use UnknowL\player\LinesiaPlayer;

class ShopData
{
	private int $quantities;
	public int $id;
	public function __construct(private string $player, private int $price, private ShopHandler $handler, private Item $item, private int $duration)
	{
		$this->quantities = $item->getCount();
	}

	/**
	 * @param int $quantities
	 * @param LinesiaPlayer $client
	 * @return void
	 */
	final public function buy(int $quantities, LinesiaPlayer $client): void
	{
		/**
		 * @var LinesiaPlayer $player
		 */
		$player = Server::getInstance()->getPlayerExact($this->player);
			if ($player === null)
			{
				if ($client->getEconomyManager()->reduce($this->price * $quantities))
				{

					$data = Server::getInstance()->getOfflinePlayerData($this->player)?->safeClone();
					if ($data === null) return;
					$properties = $data->getCompoundTag("properties");
					$properties->getCompoundTag("economy")
						->setFloat("money", $properties->getCompoundTag("economy")->getTag("money")->getValue() + $this->price * $quantities);
					Server::getInstance()->saveOfflinePlayerData("properties", $properties);
					$item = clone $this->getItem();
					$item->getNamedTag()->removeTag("MarketId");
					$item->setLore([""]);
					$client->getInventory()->addItem($item->setCount($quantities));
					$this->item->setCount($this->quantities - $quantities);

					$client->sendMessage("§aVotre achat à été effectué avec succés");

					if (($this->quantities - $quantities) <= 0)
					{
						Handler::SHOP()->removeSellable($this->id);
						return;
					}
					$this->quantities -= $quantities;
					return;
				}

				$client->sendMessage("§cVous n'avez pas assez d'argent");
			}

			if(!is_null($player) && $client->getEconomyManager()->transfer($this->price * $quantities, $player))
			{
				$player->sendMessage(sprintf("§aVotre objet %s à été vendu %d fois pour un total de %d$", $this->item->getName(), $quantities, $quantities * $this->price));
				$item = clone $this->getItem();
				$item->setLore([""]);
				$client->getInventory()->addItem($item->setCount($quantities));
				$this->item->setCount($this->quantities - $quantities);

				if (($this->quantities - $quantities) <= 0)
				{
					Handler::SHOP()->removeSellable($this->id);
					return;
				}
				$this->quantities -= $quantities;
			}

	}

	final public function format(): array
	{
		return [
			"id" => $this->id,
			"player" => $this->player,
			"price" => $this->price,
			"item" => base64_encode((new LittleEndianNbtSerializer())->write(new TreeRoot($this->item->nbtSerialize()))),
			"quantities" => $this->quantities,
			"duration" => $this->duration
		];
	}

	/**
	 * @return ShopHandler
	 */
	public function getHandler(): ShopHandler
	{
		return $this->handler;
	}

	/**
	 * @return string
	 */
	public function getPlayer(): string
	{
		return $this->player;
	}

	/**
	 * @return int
	 */
	public function getPrice(): int
	{
		return $this->price;
	}

	/**
	 * @return Item
	 */
	public function getItem(): Item
	{
		return $this->item;
	}


	/**
	 * @return int
	 */
	public function getId(): int
	{
		return $this->id;
	}

	/**
	 * @return int
	 */
	public function getQuantities(): int
	{
		return $this->quantities;
	}

	/**
	 * @return int
	 */
	final public function getDuration(): int
	{
		return $this->duration;
	}

}