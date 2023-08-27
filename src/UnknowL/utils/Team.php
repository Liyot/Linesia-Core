<?php

namespace UnknowL\utils;

use pocketmine\block\utils\DyeColor;
use UnknowL\player\LinesiaPlayer;

final class Team
{

	private DyeColor $color;

	public function __construct(private array $players, private string $name) {}

	final public function addPlayer(LinesiaPlayer $player): void
	{
		$this->players[$player->getUniqueId()->toString()] ??= $player;
	}

	final public function getPlayer(string $xuid): ?LinesiaPlayer
	{
		return $this->players[$xuid] ?? null;
	}

	final public function getColor(): DyeColor
	{
		return $this->color;
	}

	final public function setColor(DyeColor $color): void
	{
		$this->color = $color;
	}

	final public function getPlayers(): array
	{
		return $this->players;
	}

}