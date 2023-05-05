<?php

namespace UnknowL\player\manager;

use UnknowL\player\LinesiaPlayer;

final class EconomyManager extends PlayerManager
{

	private int $money = 0;

	public function __construct(protected LinesiaPlayer $player)
	{
		parent::__construct($player);
	}

	final public function getName(): string
	{
		return "Economy";
	}

	protected final function load(): void
	{
		$this->money = $this->player->getPlayerProperties()->getNestedProperties("manager.economy.money") ?? 0;
	}

	final public function reduce(int $amount): void
	{
		$this->money + $amount < 0 ? $this->money = 0 : $this->money -= $amount;
		$this->player->sendMessage("Votre monnaie à été réduite de $amount monnaie");
	}

	final public function add(int $amount): void
	{
		$this->money += $amount;
		$this->player->sendMessage("Votre monnaie à été augmenté de $amount");
	}

	final public function set(int $amount): void
	{
		$this->money = abs($amount);
		$this->player->sendMessage("Votre monnaie à été défini à $amount monnaie");
	}

	final public function transfer(int $amount, LinesiaPlayer $target): bool
	{
		if($amount <= $this->money)
		{
			$target->getEconomyManager()->add($amount);
			$this->player->sendMessage(sprintf("Vous avez transférer %d monnaie à %s", $amount, $target->getName()));
			$target->sendMessage(sprintf("Le joueur %s vous à transférer %d monnaie", $target->getName(), $amount));
			return true;
		}
		return false;
	}

	final public function getMoney(): int
	{
		return $this->money;
	}

	final public function getAll(): mixed
	{
		return [
			"money" => $this->money
		];
	}
}