<?php

namespace UnknowL\casino\types;

use UnknowL\player\LinesiaPlayer;

interface IGame
{
	public function getName(): string;

	public function getDescription(): string;

	public function start(LinesiaPlayer $player, int $mise): void;

	public function win(LinesiaPlayer $player, int $gain): void;

	public function loose(LinesiaPlayer $player): void;
	
}