<?php

namespace UnknowL\task;

use pocketmine\event\Event;
use pocketmine\event\player\PlayerChatEvent;
use pocketmine\scheduler\Task;
use pocketmine\Server;
use UnknowL\listener\ISharedListener;
use UnknowL\player\LinesiaPlayer;

/*Maybe add a random gain*/
class ChatGameTask extends Task implements ISharedListener
{

	const GAME_MIXED_WORDS = 0;
	const GAME_CALC = 1;
	const GAME_FASTEST = 2;

	private string $expectedResponse = "";


	private array $words = [];

    /**
     * @inheritDoc
     */
    public function onRun(): void
    {
		$this->createGame();
	}

	public function getExpectedResponse(): string
	{
		return $this->expectedResponse;
	}

	/**@var PlayerChatEvent $event*/
	public function onEvent(Event $event): void
	{
		/**@var LinesiaPlayer $player*/
		$player = $event->getPlayer();
		$message = $event->getMessage();

		if ($message === $this->getExpectedResponse())
		{
			$player->getEconomyManager()->add(100);
			Server::getInstance()->broadcastMessage(sprintf("§aLe joueur %s à gagner 100$ en répondant correctement !", $player->getName()));
		}
	}

	private function createGame(): void
	{
		switch (mt_rand(0, 2))
		{
			case 0:
				$word = str_shuffle($this->words[array_rand($this->words)]);
				Server::getInstance()->broadcastMessage(sprintf("§5§lRetrouvez le mot suivant %s pour gagner 100$", $word));
				$this->expectedResponse = $word;
				break;

			case 1:
				$number1 = random_int(1, 100);
				$number2 = random_int(1, 100);
				$rand = random_int(0, 3);
				$array = ['+', '-', '*', '/'];
				Server::getInstance()->broadcastMessage(sprintf("§5§lEffectuez le calcul %s %s %s pour gagner 1100$", $number1, $array[$rand], $number2));
				$this->expectedResponse = match ($rand)
				{
					0 => $number1 + $number2,
                    1 => $number1 - $number2,
                    2 => $number1 * $number2,
                    3 => $number1 / $number2,
				};
				break;

            case 2:
				$word = $this->words[array_rand($this->words)];
				Server::getInstance()->broadcastMessage(sprintf("§5§lEcrivez le mot %s le plus rapidement pour gagner 100$", $word));
				$this->expectedResponse = $word;
		}
	}

	public function getEventName(): string
	{
		return PlayerChatEvent::class;
	}
}