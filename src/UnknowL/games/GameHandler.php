<?php

namespace UnknowL\games;

use pocketmine\math\AxisAlignedBB;
use pocketmine\scheduler\Task;
use UnknowL\commands\event\args\GameArgument;
use UnknowL\games\types\KothGame;
use UnknowL\games\types\OutpostGame;
use UnknowL\handlers\Handler;
use UnknowL\Linesia;

final class GameHandler extends Handler
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
		$this->addGame(new KothGame(new AxisAlignedBB(299, 145, 395, 306, 148, 402)), GamesEnum::KOTH);
		//$this->addGame(new OutpostGame());
	}

	final public function addGame(BaseGame $game, string $gamesEnum): void
	{
		$this->games[$gamesEnum] = $game;
		GameArgument::$VALUES[$gamesEnum] = $game;
		$game->start();
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