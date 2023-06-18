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
		$array = [];
		foreach (Server::getInstance()->getWorldManager()->getWorldByName($this->world)->getEntities() as $entity)
		{
			if($entity instanceof ItemEntity) $entity->flagForDespawn();
			yield $count++;
		}
	}

	public function onRun(): void
	{
		Server::getInstance()->broadcastMessage(new Translatable(count(iterator_to_array($this->clear())). " entitées ont été clear") );
	}
}