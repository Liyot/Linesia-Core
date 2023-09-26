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

	private string $world = "linesia";

	public static int $time = 100 * 20;

	final public function clear(): \Generator
	{
		self::$time = 300 * 20;
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
		self::$time !== 0 ? $message = match (self::$time)
		{
			30 * 20 => "§d§l» §r§fLe prochain clearlagg aura lieu dans §d30 §fseconde(s) !",
			10 * 20 => "§d§l» §r§fLe prochain clearlagg aura lieu dans §d10 §fseconde(s) !",
			3 * 20 =>"§d§l» §r§fLe prochain clearlagg aura lieu dans §d3 §fseconde(s) !",
			2 * 20 => "§d§l» §r§fLe prochain clearlagg aura lieu dans §d2 §fseconde(s) !",
			20 =>"§d§l» §r§fLe prochain clearlagg aura lieu dans §d1 §fseconde(s) !",
			default => ''

		} : $message = "§d§l» §r§fLe clearlag à clear " . count(iterator_to_array($this->clear())). " entitées !";
		self::$time--;
		$message === '' ?: Server::getInstance()->broadcastMessage(new Translatable($message));
	}
}