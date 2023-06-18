<?php

namespace UnknowL\listener;

use pocketmine\block\VanillaBlocks;
use pocketmine\event\Listener;
use pocketmine\event\player\PlayerChatEvent;
use pocketmine\event\player\PlayerCreationEvent;
use pocketmine\event\player\PlayerJoinEvent;
use UnknowL\lib\forms\MenuForm;
use UnknowL\player\LinesiaPlayer;

final class PlayerListener implements Listener
{

	public function onCreation(PlayerCreationEvent $event)
	{
		$event->setPlayerClass(LinesiaPlayer::class);
	}

	public function onJoin(PlayerJoinEvent $event)
	{
		$event->getPlayer()->sendForm(new MenuForm("test"));
	}

	public function onChat(PlayerChatEvent $event)
	{
		/**@var LinesiaPlayer $player*/
		$player = $event->getPlayer();
		//$event->setMessage($player->getRank()->handleMessage($event->getMessage()), "");
	}
}