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

	private int $time = 300 * 20;

	final public function clear(): \Generator
	{
		$this->time = 300 * 20;
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
		$this->time === 0 ? $message = match ($this->time)
		{
			30 => "§d§l» §r§fLe prochain clearlagg aura lieu dans §d30 §fseconde(s) !",
			10 => "§d§l» §r§fLe prochain clearlagg aura lieu dans §d10 §fseconde(s) !",
			3 =>"§d§l» §r§fLe prochain clearlagg aura lieu dans §d$3 §fseconde(s) !",
			2 => "§d§l» §r§fLe prochain clearlagg aura lieu dans §d2 §fseconde(s) !",
			1 =>"§d§l» §r§fLe prochain clearlagg aura lieu dans §d1 §fseconde(s) !"


		} : $message = count(iterator_to_array($this->clear())). " entitées ont été clear";
		$this->time--;
		Server::getInstance()->broadcastMessage(new Translatable($message));
	}
}