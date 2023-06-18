<?php

namespace UnknowL\casino\types;

use UnknowL\player\LinesiaPlayer;

class Pyramides extends CasinoGame
{

    public function getName(): string
    {
		return "Pyramides";
	}

    public function getDescription(): string
    {
		return "Choississez une mise et multiplier la par la somme du hopper dans lequel elle tombe";
	}

    public function start(LinesiaPlayer $player, int $mise): void
    {

	}

    public function win(LinesiaPlayer $player, int $gain): void
    {
        // TODO: Implement win() method.
    }

    public function loose(LinesiaPlayer $player): void
    {
        // TODO: Implement loose() method.
    }
}