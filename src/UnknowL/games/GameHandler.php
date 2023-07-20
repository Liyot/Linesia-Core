<?php

namespace UnknowL\games;

use pocketmine\math\AxisAlignedBB;
use pocketmine\scheduler\Task;
use UnknowL\games\types\KothGame;
use UnknowL\handlers\Handler;
use UnknowL\Linesia;

class GameHandler extends Handler
{

	/**@var BaseGame[] $games*/
	private array $games = [];

	public function __construct()
	{
		parent::__construct();
	}

	protected function loadData(): void
    {
		$this->initGames();
		Linesia::getInstance()->getScheduler()->scheduleRepeatingTask(new class extends Task
		{
			public function onRun(): void
			{
				foreach (Handler::GAME()->getGames() as $game)
				{
					$game->onTick();
				}
			}
		}, 20);
	}

    protected function saveData(): void
    {
		//Noop
	}

	/**@return BaseGame[]*/
	final public function getGames(): array
	{
		return $this->games;
	}

	final public function initGames(): void
	{
		$this->games[GamesEnum::KOTH] = (new KothGame(new AxisAlignedBB(299, 145, 395, 306, 148, 402)))->start();
	}
	final public function getGame(string $game)
	{
		return $this->games[$game];
	}

    public function getName(): string
    {
		return "Game";
	}
}