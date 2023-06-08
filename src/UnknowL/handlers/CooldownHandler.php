<?php

namespace UnknowL\handlers;

use DateTime;
use pocketmine\event\entity\ExplosionPrimeEvent;
use pocketmine\scheduler\ClosureTask;
use pocketmine\scheduler\Task;
use pocketmine\utils\SingletonTrait;
use UnknowL\Linesia;
use UnknowL\player\LinesiaPlayer;
use UnknowL\handlers\dataTypes\Cooldown;

final class CooldownHandler
{
	/**
	 * @var Cooldown[]
	 */
	private array $list = [];

	public function __construct()
	{
		Linesia::getInstance()->getScheduler()->scheduleRepeatingTask(new class extends Task
			{
				public function onRun(): void
				{
					Linesia::getInstance()->getCooldownHandler()->update();
				}
			}, 20);
	}

	final public function add(Cooldown $cooldown): void
	{
		$this->list[] = $cooldown;
	}

	final public function update(): void
	{
		foreach ($this->list as $key => $cooldown)
		{
			if($cooldown->isFinish())
			{
				unset($this->list[$key]);
				continue;
			}
		}
	}

	final public function saveAll(): void
	{
		foreach ($this->list as $cooldown)
		{
			$cooldown->save();
		}
	}
}