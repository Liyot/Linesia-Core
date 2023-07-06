<?php

namespace UnknowL\task;

use pocketmine\entity\object\ItemEntity;
use pocketmine\lang\Translatable;
use pocketmine\scheduler\AsyncTask;
use pocketmine\scheduler\Task;
use pocketmine\Server;
use UnknowL\Linesia;

class ClearlagTask extends Task
{

	private string $world = "world";

	final public function clear(): \Generator
	{
		$count = 0;
		foreach (Server::getInstance()->getWorldManager()->getWorldByName($this->world)->getEntities() as $entity)
		{
			if($entity instanceof ItemEntity)
			{
				$entity->flagForDespawn();
				yield $count++;
			}
		}
	}

	public function onRun(): void
	{
        $count = count(iterator_to_array($this->clear()));
		Server::getInstance()->broadcastMessage(new Translatable("§d§l» §r§fIl y a eu un total de§d $count §fentitée(s) supprimé !"));
	}
}