<?php

namespace UnknowL\listener;

use pocketmine\event\Listener;
use pocketmine\event\player\PlayerCreationEvent;
use UnknowL\player\LinesiaPlayer;

final class PlayerListener implements Listener
{

	public function onCreation(PlayerCreationEvent $event)
	{
		$event->setPlayerClass(LinesiaPlayer::class);
	}

}