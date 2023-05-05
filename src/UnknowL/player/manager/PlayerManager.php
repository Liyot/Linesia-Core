<?php

namespace UnknowL\player\manager;

use UnknowL\player\LinesiaPlayer;

abstract class PlayerManager
{

	public function __construct(protected LinesiaPlayer $player)
	{
		$this->load();
	}

	final public function getPlayer(): LinesiaPlayer
	{
		return $this->player;
	}

	final public function save(): void
	{
		$properties = $this->player->getPlayerProperties();
		$properties->setNestedProperties("maganer.".strtolower($this->getName()), $this->getAll());
	}

	abstract protected function load(): void;

	abstract public function getAll(): mixed;

	abstract public function getName(): string;

}