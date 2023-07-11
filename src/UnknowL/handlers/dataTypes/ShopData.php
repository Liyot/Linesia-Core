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
	private int $id ;
	public function __construct(private string $player, private int $price, private ShopHandler $handler, private Item $item, private int $duration)
	{
		$this->id = Handler::SHOP()->generateId();
		$this->quantities = $this->item->getCount();
	}

//	final public function getForm(): BaseForm
//	{
//		$form = new CustomForm(sprintf("%s de %s", $this->item->getName(), $this->player),
//			[new Label($this->description), new Slider("Quantités", 1, $this->quantities), new Button("Retour")], function (LinesiaPlayer $player, CustomFormResponse $response)
//			{
//				$this->buy($response->getSlider()->getValue(), $player);
//			}
//		);
//
//		return $form;
//	}

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
			$data = Server::getInstance()->getOfflinePlayerData($this->player)->safeClone();
			$properties = $data->getCompoundTag("properties");
			$properties->getCompoundTag("manager")->getCompoundTag("economy")
				->setInt("money", $properties->getCompoundTag("manager")->getCompoundTag("economy")->getInt("money") + $this->price * $quantities);
			Server::getInstance()->saveOfflinePlayerData("properties", $properties);

			$client->getInventory()->addItem($this->getItem()->setCount($quantities));

			$client->sendMessage("Votre achat à été effectué avec succés");
		}

		if(!is_null($player) && $player->getEconomyManager()->transfer($this->price * $quantities, $client))
		{
			$player->sendMessage(sprintf("Votre objet %s à été vendu %d fois pour un total de %d$", $this->item->getName(), $quantities, $quantities * $this->price));
			$client->getInventory()->addItem($this->getItem()->setCount($quantities));
		}
		if ($this->quantities === $quantities)
		{
			$this->handler->removeSellable($this->id);
			return;
		}

		$this->quantities -= $quantities;
	}

	final public function format(): array
	{
		return [
			"id" => $this->id,
			"player" => $this->player,
			"price" => $this->price,
			"item" => (new LittleEndianNbtSerializer())->write(new TreeRoot($this->item->nbtSerialize())),
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

}