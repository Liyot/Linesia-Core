<?php

namespace UnknowL\listener;

use pocketmine\block\VanillaBlocks;
use pocketmine\event\Listener;
use pocketmine\event\player\PlayerChatEvent;
use pocketmine\event\player\PlayerCreationEvent;
use pocketmine\event\player\PlayerItemHeldEvent;
use pocketmine\event\player\PlayerItemUseEvent;
use pocketmine\event\world\WorldUnloadEvent;
use pocketmine\item\ItemIds;
use pocketmine\item\VanillaItems;
use UnknowL\player\LinesiaPlayer;

final class PlayerListener implements Listener
{

	public function onCreation(PlayerCreationEvent $event)
	{
		$event->setPlayerClass(LinesiaPlayer::class);
	}

	public function onChat(PlayerChatEvent $event)
	{
		/**@var LinesiaPlayer $player*/
		$player = $event->getPlayer();
		//$event->setMessage($player->getRank()->handleMessage($event->getMessage()), "");
	}
}