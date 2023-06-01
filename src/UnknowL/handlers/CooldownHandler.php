<?php

namespace UnknowL\handlers;

use pocketmine\event\entity\ExplosionPrimeEvent;
use pocketmine\scheduler\ClosureTask;
use pocketmine\utils\SingletonTrait;
use UnknowL\Linesia;
use UnknowL\player\LinesiaPlayer;
use UnknowL\utils\Cooldown;

final class CooldownHandler
{
	/**
	 * @var Cooldown[]
	 */
	private array $list = [];

	public function __construct()
	{
		Linesia::getInstance()->getScheduler()->scheduleRepeatingTask(new ClosureTask(
			function ()
			{
				$this->update();
			}
		), 20);
	}

	final public function unserizalize(string $data): Cooldown
	{
		$data = explode(":", $data);
		return new Cooldown(function() {}, $data[0], $data[1], $data[2], $data[3], $data[4]);
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
			$cooldown->actualize();
		}
	}
}