<?php

namespace UnknowL\listener;

use pocketmine\block\VanillaBlocks;
use pocketmine\entity\Entity;
use pocketmine\entity\EntityFactory;
use pocketmine\entity\Zombie;
use pocketmine\event\block\BlockPlaceEvent;
use pocketmine\event\entity\EntitySpawnEvent;
use pocketmine\event\Listener;
use pocketmine\event\player\PlayerChatEvent;
use pocketmine\event\player\PlayerCreationEvent;
use pocketmine\event\player\PlayerJoinEvent;
use pocketmine\inventory\CreativeInventory;
use pocketmine\item\ItemFactory;
use pocketmine\math\Vector3;
use pocketmine\nbt\tag\CompoundTag;
use pocketmine\network\mcpe\protocol\types\entity\EntityIds;
use pocketmine\scheduler\Task;
use pocketmine\world\format\Chunk;
use pocketmine\world\Position;
use UnknowL\events\CooldownExpireEvent;
use UnknowL\handlers\dataTypes\PlayerCooldown;
use UnknowL\lib\forms\MenuForm;
use UnknowL\Linesia;
use UnknowL\player\LinesiaPlayer;
use UnknowL\player\manager\StatManager;
use UnknowL\utils\PathLoader;

final class PlayerListener implements Listener
{

	public function onCreation(PlayerCreationEvent $event)
	{
		$event->setPlayerClass(LinesiaPlayer::class);
	}

	public function onJoin(PlayerJoinEvent $event)
	{
	}

	public function onPlace(BlockPlaceEvent $event)
	{
		/**@var LinesiaPlayer $player*/
		$player = $event->getPlayer();

		if (!$event->isCancelled()) $player->getStatManager()->handleEvents(StatManager::TYPE_BLOCK_PLACED);
	}

    public function onCooldownExpire(CooldownExpireEvent $event)
    {
        $cooldown = $event->getCooldown();
        $player = $event->getPlayer();
        if(!is_null($player))
        {
            /**@var PlayerCooldown $cooldown*/

            switch ($cooldown->getPath())
            {
                case PathLoader::PATH_RANK_CACHE:

            }
        }
    }

	public function onChat(PlayerChatEvent $event)
	{
		/**@var LinesiaPlayer $player*/
		$player = $event->getPlayer();
		//$event->setMessage($player->getRank()->handleMessage($event->getMessage()), "");
	}
}