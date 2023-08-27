<?php

namespace UnknowL\games\types;

use UnknowL\games\BaseGame;
use UnknowL\player\LinesiaPlayer;

class NexusGame extends BaseGame
{

    public function join(LinesiaPlayer $player): void
    {

	}

    public function getName(): string
    {
		return "Nexus";
	}

	public function leave(LinesiaPlayer $player): void
	{

	}

	public function onTick(): void
	{
		// TODO: Implement onTick() method.
	}
}