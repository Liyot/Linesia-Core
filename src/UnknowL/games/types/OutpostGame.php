<?php

namespace UnknowL\games\types;

use DaPigGuy\PiggyFactions\PiggyFactions;
use FG\ASN1\Universal\Boolean;
use pocketmine\math\AxisAlignedBB;
use pocketmine\Server;
use UnknowL\games\BaseGame;
use UnknowL\player\LinesiaPlayer;

//implementer une class commune avec KothGame?
final class OutpostGame extends BaseGame
{

	const PHASE_CAPTURING = 1;
	const PHASE_CAPTURED = 2;
	const PHASE_NONE = 3;

	/**@var list<string<list<LinesiaPlayer>> */
	private array $players = [];

	public string|null $capturingFaction = null;

	public ?string $capturedFaction = null;
	private int $capturePoints = 0;

	private int $phase = 0;


	public function __construct(private AxisAlignedBB $zone){}

    public function join(LinesiaPlayer $player): void
    {
		$faction = PiggyFactions::getInstance()->getPlayerManager()->getPlayer($player)?->getFaction()?->getName() ?? null;
		is_null($faction) ? $player->sendMessage("Vous devez avoir une faction") : $this->players[$faction][$player->getXuid()] = $player;
	}

	public function hasJoined(LinesiaPlayer $player): bool
	{
		$faction = PiggyFactions::getInstance()->getPlayerManager()->getPlayer($player)?->getFaction()?->getName() ?? "";
		return isset($this->players[$faction][$player->getXuid()]) ?? false;
	}

    public function getName(): string
    {
		return "Outpost";
	}

	public function onTick(): void
	{
		if (!$this->hasStarted()) return;
		/**@var LinesiaPlayer $player*/
		foreach (Server::getInstance()->getOnlinePlayers() as $player)
		{
			if ($this->zone->isVectorInside($player->getPosition()->asVector3()) && $player->getWorld()->getFolderName() === "linesia")
			{
				if (!$this->hasJoined($player)) $this->join($player);

				$this->gainOrRemoveFactions();
				continue;
			}
			if ($this->hasJoined($player))
			{
				$this->leave($player);
			}
		}
		switch (true)
		{
			case $this->phase === self::PHASE_CAPTURING && $this->capturePoints >= 120:
				$this->phase = self::PHASE_CAPTURED;
		}
	}

	public function gainOrRemoveFactions(): void
	{
		foreach ($this->players as $factionName => $players)
		{
			if ($this->capturingFaction !== null)
			{
				if ($this->isFactionEmpty($factionName))
				{
					if ($this->capturedFaction === $factionName || $this->capturingFaction === $factionName)
					{
						Server::getInstance()->broadcastMessage("L'outpost est dersomais disponible");
						$this->capturePoints = 0;
						$this->capturingFaction = null;
						$this->capturedFaction = null;
					}
					unset($this->players[$factionName]);
					continue;
				}
				if ($this->capturingFaction === $factionName)
				{
					$this->capturePoints++;
					array_map(fn(LinesiaPlayer $player) => $player->sendPopup($this->capturePoints), $this->players[$factionName]);
					continue;
				}
				continue;
			}
			$this->capturingFaction = $factionName;
			Server::getInstance()->broadcastMessage(sprintf("L'outpost est possédés par %s", $factionName));
		}
	}

	public function isFactionEmpty(string $faction): bool
	{
		/**@var LinesiaPlayer $player*/
		foreach ($this->players[$faction] as $xuid => $player)
		{
			if (($player->isAlive() || $player->isConnected()) || $this->zone->isVectorInside($player->getPosition()->asVector3()))
			{
				if (empty($this->players[$faction]))
				{
					return true;
				}
			}
		}
		return false;
	}

	public function leave(LinesiaPlayer $player): void
	{
		// TODO: Implement leave() method.
	}
}