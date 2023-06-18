<?php

namespace UnknowL\handlers;

use DateTime;
use pocketmine\scheduler\Task;
use UnknowL\Linesia;
use UnknowL\player\LinesiaPlayer;
use UnknowL\handlers\dataTypes\Cooldown;

final class CooldownHandler extends Handler
{
	/**
	 * @var Cooldown[]
	 */
	private array $list = [];

	public function __construct()
	{
		parent::__construct();
		Linesia::getInstance()->getScheduler()->scheduleRepeatingTask(new class extends Task
			{
				public function onRun(): void
				{
					Handler::COOLDOWN()->update();
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

	protected function loadData(): void {}

	protected function saveData(): void{}

	public function getName(): string
	{
		return "Cooldown";
	}
}