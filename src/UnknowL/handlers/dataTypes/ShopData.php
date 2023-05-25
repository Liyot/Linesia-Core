<?php

namespace UnknowL\handlers\dataTypes;

use pocketmine\item\Item;
use pocketmine\Server;
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

	protected int $id;

	public function __construct(private string $player, private string $name, private int $price, private ShopHandler $handler, private Item $item, private int $quantities = 1, private string $description = "", private string $category = "all")
	{
		$this->id = $handler->generateId($category);
	}

	final public function getForm(): BaseForm
	{
		$form = new CustomForm(sprintf("%s de %s", $this->item->getName(), $this->player),
			[new Label($this->description), new Slider("Quantités", 1, $this->quantities), new Button("Retour")], function (LinesiaPlayer $player, CustomFormResponse $response)
			{
				$this->buy($response->getSlider()->getValue(), $player);
			}
		);

		return $form;
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
		if($player->getEconomyManager()->transfer($this->price * $quantities, $client))
		{
			$player->sendMessage(sprintf("Votre objet %s à été vendu %d fois pour un total de %d$", $this->name, $quantities, $quantities * $this->price));
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
			"name" => $this->name,
			"player" => $this->player,
			"price" => $this->price,
			"item" => $this->item->jsonSerialize(),
			"quantities" => $this->quantities,
			"description" => $this->description,
			"category" => $this->category
		];
	}



	/**
	 * @return string
	 */
	public function getName(): string
	{
		return $this->name;
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
	 * @return string
	 */
	public function getDescription(): string
	{
		return $this->description;
	}

	/**
	 * @return int
	 */
	public function getId(): int
	{
		return $this->id;
	}

	/**
	 * @return string
	 */
	public function getCategory(): string
	{
		return $this->category;
	}

	/**
	 * @return int
	 */
	public function getQuantities(): int
	{
		return $this->quantities;
	}

}