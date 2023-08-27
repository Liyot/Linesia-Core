<?php

namespace UnknowL\handlers\dataTypes\requests;

use pocketmine\inventory\PlayerInventory;
use UnknowL\handlers\Handler;
use UnknowL\player\LinesiaPlayer;
use UnknowL\utils\PositionUtils;

final class DualRequest extends Request
{

	const TEAM_BLUE = 1;
	const TEAM_RED = 2;
	/**
	 * @var PlayerInventory[]
	 */
	private array $savedStuff = [];

	public function __construct(private LinesiaPlayer $from, private LinesiaPlayer $to, private array $kit = [], private int $mise = 1)
	{
		parent::__construct($to, $from);
	}

	public function getName(): string
    {
		return "duel";
	}

    public function getChatFormat(): string
    {
		return ucfirst($this->getName());
	}

	public function send(): void
	{
		$this->getTo()->awaitChatResponse(function(LinesiaPlayer $player, mixed $args)
		{
			$args === "accept" ? $this->accept() : $this->decline();
		});
		parent::send();
	}

	final public function accept(): void
	{
		$this->getFrom()->teleport(PositionUtils::getAvailableDualRoom(PositionUtils::DUAL_1V1));
		$this->getTo()->teleport(PositionUtils::getAvailableDualRoom(PositionUtils::DUAL_1V1)->add(5, 0, 0));

		$this->getFrom()->setInDual();
		$this->getTo()->setInDual();

		$this->saveInventories();
		if (!empty($this->getSelectedKit()))
		{
			$player = $this->getFrom();
			$player->getInventory()->setContents($this->getSelectedKit()["inventory"]);
			$player->getArmorInventory()->setContents($this->getSelectedKit()["armor"]);

			$player = $this->getTo();
			$player->getInventory()->setContents($this->getSelectedKit()["inventory"]);
			$player->getArmorInventory()->setContents($this->getSelectedKit()["armor"]);
		}
	}

	final public function win(LinesiaPlayer $player)
	{
		if ($player->getUniqueId()->toString() === $this->from->getUniqueId()->toString() || $player->getUniqueId()->toString() === $this->to->getUniqueId()->toString())
		{
			$player->getEconomyManager()->add($this->mise);
			$player->setBaseActiveInteraction();
			$player->teleport(PositionUtils::getSpawnPosition());
			$player->setInDual(false);

			$looser = $this->from->getUniqueId()->toString() === $player->getUniqueId()->toString() ? $this->to : $this->from;
			$looser->getEconomyManager()->reduce($this->mise);
			$looser->setBaseActiveInteraction();
			$looser->teleport(PositionUtils::getSpawnPosition());
			$player->setInDual(false);

			$this->updateInteraction();
			$this->sendInventories();

			Handler::REQUEST()->removeRequest($this);
		}
	}

	private function sendInventories(): void
	{
		$this->from->getInventory()->setContents($this->savedStuff[]);
	}

	private function updateInteraction(): void
	{
		$player = $this->from;
		$player->setActiveInteraction(LinesiaPlayer::INTERACTION_REQUEST, false);
		$player->setActiveInteraction(LinesiaPlayer::INTERACTION_BREAK, false);
		$player->setActiveInteraction(LinesiaPlayer::INTERACTION_COMMAND, false);

		$player = $this->to;
		$player->setActiveInteraction(LinesiaPlayer::INTERACTION_REQUEST, false);
		$player->setActiveInteraction(LinesiaPlayer::INTERACTION_BREAK, false);
		$player->setActiveInteraction(LinesiaPlayer::INTERACTION_COMMAND, false);
	}

	private function saveInventories(): void
	{
		$player = $this->getFrom();
		$inventory = $player->getInventory()->getContents(true);
		$armorInventory = $player->getArmorInventory()->getContents(true);
		$this->savedStuff[$player->getUniqueId()->toString()]["inventory"] = $inventory;
		$this->savedStuff[$player->getUniqueId()->toString()]["armor"] = $armorInventory;

		$player = $this->getTo();
		$inventory = $player->getInventory()->getContents(true);
		$armorInventory = $player->getArmorInventory()->getContents(true);
		$this->savedStuff[$player->getUniqueId()->toString()]["inventory"] = $inventory;
		$this->savedStuff[$player->getUniqueId()->toString()]["armor"] = $armorInventory;
	}

	public function getSelectedKit(): array
	{
		return $this->kit;
	}
}