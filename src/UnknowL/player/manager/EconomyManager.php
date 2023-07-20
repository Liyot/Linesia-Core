<?php

namespace UnknowL\player\manager;

use UnknowL\api\ScoreBoardAPI;
use UnknowL\player\LinesiaPlayer;

final class EconomyManager extends PlayerManager
{

	private int|float $money = 0;

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
		$this->money = $this->player->getPlayerProperties()->getNestedProperties("economy.money") ?? 0;
	}

	final public function reduce(int|float $amount): bool
	{
		if ($this->money >= $amount)
		{
			$this->money -= $amount;
            ScoreBoardAPI::updateMoney($this->player);
			$this->player->sendMessage("§cVous avez perdu $amount $");
			return true;
		}
		$this->player->sendMessage("§cVous n'avez pas assez de monnaie!");
		return false;
	}

	final public function add(int|float $amount): void
	{
		$this->money += $amount;
        ScoreBoardAPI::updateMoney($this->player);
		$this->player->sendMessage("§aVous avez gagné $amount $");
	}

	final public function set(int|float $amount): void
	{
		$this->money = abs($amount);
        ScoreBoardAPI::updateMoney($this->player);
		$this->player->sendMessage("§cVotre monnaie a été set à $amount $");
	}

	final public function transfer(int|float $amount, LinesiaPlayer $target, bool $message = false): bool
	{
		if($this->money >= $amount)
		{
			$target->getEconomyManager()->add($amount);
			$this->reduce($amount);
			if($message)
			{
                ScoreBoardAPI::updateMoney($this->player);
                ScoreBoardAPI::updateMoney($target);
				$this->player->sendMessage(sprintf("§aVous avez transférer %d $ à %s", $amount, $target->getName()));
				$target->sendMessage(sprintf("§aLe joueur %s vous à transférer %d $", $target->getName(), $amount));
			}
			return true;
		}
		$this->player->sendMessage("§cLa transaction à échouée");
		return false;
	}

	final public function getMoney(): int|float
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