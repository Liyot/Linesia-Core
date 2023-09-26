<?php

namespace UnknowL\games;

use pocketmine\Server;
use UnknowL\player\LinesiaPlayer;

abstract class BaseGame
{

	private int $time = 0;
	private bool $hasStarted = false;
	abstract public function join(LinesiaPlayer $player): void;
	abstract public function leave(LinesiaPlayer $player): void;

	public function stop(): void
	{
		$this->hasStarted = false;
		Server::getInstance()->broadcastMessage(sprintf("[Linesia] Le %s s'est subitement arréter", $this->getName()));
	}

	abstract public function getName(): string;

	public function start(): self
	{
		Server::getInstance()->broadcastMessage("[Linesia] ". $this->getName(). " à démarré!");
		$this->hasStarted = true;
		return $this;
	}

	final public function hasStarted(): bool
	{
		return $this->hasStarted;
	}

	protected function win(LinesiaPlayer $player): void
	{
		$this->hasStarted = false;
		$player->sendMessage("[Linesia] Vous avez gagné l'event ". $this->getName());
	}

	abstract public function onTick(): void;
}