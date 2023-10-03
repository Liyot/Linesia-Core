<?php

namespace UnknowL\handlers\dataTypes\requests;


use pocketmine\block\utils\DyeColor;
use pocketmine\color\Color;
use pocketmine\event\Event;
use pocketmine\event\player\PlayerDeathEvent;
use pocketmine\form\Form;
use pocketmine\world\Position;
use UnknowL\lib\forms\ModalForm;
use UnknowL\listener\ISharedListener;
use UnknowL\player\LinesiaPlayer;
use UnknowL\utils\PositionUtils;
use UnknowL\utils\Team;

final class MultiDualRequest extends Request implements ISharedListener
{

	/**
	 * @param list<string<LinesiaPlayer>> $players
	 */
	private array $players = [];

	/**
	 * @param list<string<LinesiaPlayer>> $players
	 */
	private array $deadPlayers = [];

	/**
	 * @param LinesiaPlayer $originalPlayer
	 * @param Team $from
	 * @param Team $to
	 */
	public function __construct(private LinesiaPlayer $originalPlayer, protected Team $from, protected Team $to)
	{
		array_map(fn(LinesiaPlayer $player) => $this->players[$from->getColor()->name()][$player->getUniqueId()->toString()] = $player, $from->getPlayers());
		array_map(fn(LinesiaPlayer $player) => $this->players[$to->getColor()->name()][$player->getUniqueId()->toString()] = $player, $to->getPlayers());
		foreach ($this->players as $player)
		{
			$player->sendMessage(sprintf("Vous avez reçu une demande de duel 2vs2 de la part de: %s \n tapez 'yes' pour accepter ne faites rien pour refuser",
				$originalPlayer->getDisplayName()));
			$player->awaitChatResponse(function(LinesiaPlayer $player, mixed $message)
			{
				if ($message === "yes")
				{
					$this->addPlayer($player);
				}
			});
//			$player->sendForm($this->getForm($player));
		}
	}

	public function accept(): void
	{
		parent::accept();
		foreach ($this->players as $player)
		{
			$this->updateInteraction($player);
		}
	}

	private function getForm(LinesiaPlayer $player): Form
	{
		$content = "Membre de votre équipe: ". match ($this->from->getPlayer($player->getUniqueId()->toString()))
		{
			null => implode(" ", $this->to->getPlayers()),
			default => implode(" ", $this->from->getPlayers())
		};

		return ModalForm::confirm("Acceptez vous le duel?", $content, function(LinesiaPlayer $player, bool $choice)
		{
			if ($choice)
			{
				$this->addPlayer($player);
				return;
			}
			$this->decline();
		});
	}


	private function addPlayer(LinesiaPlayer $player): void
	{
		if (count($this->players) === 3)
		{
			$this->accept();
		}
		$this->players[$player->getUniqueId()->toString()] ??= $player;
	}

	private function updateInteraction(LinesiaPlayer $player): void
	{
		$player->setActiveInteraction(LinesiaPlayer::INTERACTION_REQUEST, false);
		$player->setActiveInteraction(LinesiaPlayer::INTERACTION_BREAK, false);
		$player->setActiveInteraction(LinesiaPlayer::INTERACTION_COMMAND, false);
	}

	public function process(): void
	{
		foreach ($this->players as $player)
		{
			if ($this->getResult())
			{
				$player->teleport(PositionUtils::getAvailableDualRoom(PositionUtils::DUAL_2V2));
				$teleported = [DyeColor::RED()->name() => false, DyeColor::GREEN()->name()];
				$team = $this->getTeam($player);
				$pos = $this->getPositionByTeam($team);
				$player->teleport($teleported[$team->getColor()->name()] ? Position::fromObject($pos->add(2, 0, 0), $pos->getWorld()) : $pos);
				if ($teleported[$team->getColor()->name()] === false) $teleported[$team->getColor()->name()] = true;
			}
		}
	}

	final public function getPositionByTeam(Team $team): Position
	{
		return match ($team->getColor())
		{
			DyeColor::RED() => new Position(),
			DyeColor::BLUE() => new Position()
		};
	}

	final public function win(Team $team): void
	{

	}

	final public function isInDual(LinesiaPlayer $player)
	{
		return in_array($player, $this->players[DyeColor::RED()->name()], true) || in_array($player, $this->players[DyeColor::BLUE()->name()], true);
	}

	final public function getTeam(LinesiaPlayer $player): Team
	{
		return in_array($this->players[DyeColor::RED()->name()], $this->players) ? $this->from : $this->to;
	}

	final public function getOppositeTeam(Team $team): Team
	{
		if ($team->getColor()->name()) {}
	}

	public function getName(): string
    {
		return '2v2dual';
	}

    public function getChatFormat(): string
    {
		return "Duel 2vs2";
	}

	/**@param PlayerDeathEvent $event*/
	public function onEvent(Event $event): void
	{
		$player = $event->getPlayer();
		if ($player instanceof LinesiaPlayer && $this->isInDual($player))
		{
			if (isset($this->deadPlayers[$this->getTeam($player)->getColor()->name()])) $this->win($this->get);
			$this->deadPlayers[$this->getTeam($player)->getColor()->name()][$player->getUniqueId()->toString()] = $player;
		}
	}

	public function getEventName(): string
	{
		return PlayerDeathEvent::class;
	}
}