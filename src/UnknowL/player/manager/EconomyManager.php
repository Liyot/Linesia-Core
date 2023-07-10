<?php

namespace UnknowL\player\manager;

use UnknowL\api\ScoreBoardAPI;
use UnknowL\player\LinesiaPlayer;

final class EconomyManager extends PlayerManager
{

	private int $money = 500000;

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
		$this->money = $this->player->getPlayerProperties()->getNestedProperties("economy.money") ?? 500000;
	}

	final public function reduce(int $amount): bool
	{
		if ($this->money >= $amount)
		{
			$this->money -= $amount;
            ScoreBoardAPI::updateMoney($this->player);
			$this->player->sendMessage("Votre $ à été réduite de $amount $");
			return true;
		}
		$this->player->sendMessage("Vous n'avez pas assez de monnaie!");
		return false;
	}

	final public function add(int $amount): void
	{
		$this->money += $amount;
        ScoreBoardAPI::updateMoney($this->player);
		$this->player->sendMessage("Votre $ à été augmenté de $amount");
	}

	final public function set(int $amount): void
	{
		$this->money = abs($amount);
        ScoreBoardAPI::updateMoney($this->player);
		$this->player->sendMessage("Votre $ à été défini à $amount $");
	}

	final public function transfer(int $amount, LinesiaPlayer $target, bool $message = false): bool
	{
		if($this->money >= $amount)
		{
			$target->getEconomyManager()->add($amount);
			$this->reduce($amount);
			if($message)
			{
                ScoreBoardAPI::updateMoney($this->player);
                ScoreBoardAPI::updateMoney($target);
				$this->player->sendMessage(sprintf("Vous avez transférer %d $ à %s", $amount, $target->getName()));
				$target->sendMessage(sprintf("Le joueur %s vous à transférer %d $", $target->getName(), $amount));
			}
			return true;
		}
		$this->player->sendMessage("La transaction à échouée");
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