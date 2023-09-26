<?php

namespace UnknowL\games\types;

use pocketmine\event\Event;
use pocketmine\event\server\DataPacketReceiveEvent;
use pocketmine\network\mcpe\protocol\PlayerAuthInputPacket;
use pocketmine\player\Player;
use pocketmine\Server;
use pocketmine\world\Position;
use pocketmine\world\WorldManager;
use UnknowL\games\BaseGame;
use UnknowL\listener\ISharedListener;
use UnknowL\player\LinesiaPlayer;

class TotemGame extends BaseGame implements ISharedListener
{

	private ?LinesiaPlayer $player = null;

    public function join(LinesiaPlayer $player): void
    {

	}

    public function leave(LinesiaPlayer $player): void
    {

	}

    public function getName(): string
    {
		return "Totem";
	}

    public function onTick(): void
    {

	}

	/**
	 * @param DataPacketReceiveEvent $event
	 */
	public function onEvent(Event $event): void
	{
		$packet = $event->getPacket();
		$player = $event->getOrigin()->getPlayer();
		if ($packet instanceof PlayerAuthInputPacket)
		{
			if (!is_null($packet->getBlockActions()))
			{
				
			}
		}
	}

	public function getEventName(): string
	{
		return DataPacketReceiveEvent::class;
	}

	private function getOriginalPosition(): Position
	{
		return new Position(131, 73, 192, Server::getInstance()->getWorldManager()->getWorldByName('linesia') ?? Server::getInstance()->getWorldManager()->getDefaultWorld());
	}
}