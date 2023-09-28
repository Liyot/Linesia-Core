<?php

namespace UnknowL\games\types;

use pocketmine\entity\effect\EffectManager;
use pocketmine\math\AxisAlignedBB;
use pocketmine\scheduler\ClosureTask;
use pocketmine\Server;
use UnknowL\games\BaseGame;
use UnknowL\games\GamesEnum;
use UnknowL\handlers\Handler;
use UnknowL\Linesia;
use UnknowL\player\LinesiaPlayer;

final class KothGame extends BaseGame
{
	private array $players = [];

	public ?LinesiaPlayer $capturingPlayer = null;
	private int $capturePoints = 0;

	public function __construct(private AxisAlignedBB $zone) {}

    public function join(LinesiaPlayer $player): void
    {
		$this->players[$player->getDisplayName()] = $player->getXuid();
		$player->sendMessage("Vous rentrez dans le koth");
	}

	final public function leave(LinesiaPlayer $player): void
	{
		unset($this->players[$player->getDisplayName()]);
		$player->sendMessage("Vous sortez du koth");
	}

	public function onTick(): void
	{
		if (!$this->hasStarted()) return;
		/**@var LinesiaPlayer $player*/
		foreach (Server::getInstance()->getOnlinePlayers() as $player)
		{
			if ($this->zone->isVectorInside($player->getPosition()->asVector3()) && $player->getWorld()->getFolderName() === "linesia")
			{
				if (!in_array($player->getXuid(), $this->players, true)) $this->join($player);

				if (!is_null($this->capturingPlayer ?? null))
				{
					if (!$this->capturingPlayer->isConnected() || !$this->capturingPlayer->isAlive())
					{
						unset($this->capturingPlayer);
						Server::getInstance()->broadcastMessage("Le koth est désormais libre");
						continue;
					}
					$this->capturingPlayer->sendPopup($this->capturePoints);
					$this->capturePoints++;
					continue;
				}
				$this->capturingPlayer = $player;
				Server::getInstance()->broadcastMessage(sprintf("Le koth est désormais controlé par %s", $player->getDisplayName()));
				$this->capturePoints = 0;
				continue;
			}
			if (in_array($player->getXuid(), $this->players, true))
			{
				if (!is_null($this->capturingPlayer) && $this->capturingPlayer->getXuid() === $player->getXuid())
				{
					unset($this->capturingPlayer);
					Server::getInstance()->broadcastMessage("Le koth est désormais libre");
				}
				$this->leave($player);
			}
		}
		if ($this->capturePoints === 60 * 3 && !is_null($this->capturingPlayer)) $this->win($this->capturingPlayer);
	}

	protected function win(LinesiaPlayer $player) : void
	{
		parent::win($player);
		$player->getEconomyManager()->add(500);
		Server::getInstance()->broadcastMessage("Le prochain koth sera dans 2h!");
		Linesia::getInstance()->getScheduler()->scheduleDelayedTask(new ClosureTask( function ()
		{
			Handler::GAME()->getGame(GamesEnum::KOTH)->start();
		}), 20 * 60 * 60 * 2);
		unset($this->players, $this->capturePoints);
	}

	public function getName(): string
    {
		return "Koth";
	}
}