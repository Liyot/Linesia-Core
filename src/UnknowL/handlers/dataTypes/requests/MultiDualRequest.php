<?php

namespace UnknowL\handlers\dataTypes\requests;


use pocketmine\block\utils\DyeColor;
use pocketmine\color\Color;
use pocketmine\form\Form;
use UnknowL\lib\forms\ModalForm;
use UnknowL\player\LinesiaPlayer;
use UnknowL\utils\PositionUtils;
use UnknowL\utils\Team;

class MultiDualRequest extends Request
{

	/**
	 * @var LinesiaPlayer[] $players
	 */
	private array $players = [];

	/**
	 * @param LinesiaPlayer $from
	 * @param LinesiaPlayer[] $to
	 */
	public function __construct(private readonly LinesiaPlayer $originalPlayer, protected Team $from, protected Team $to)
	{
		array_map(fn(LinesiaPlayer $player) => $this->players[$player->getUniqueId()->toString()] = $player, $from->getPlayers());
		array_map(fn(LinesiaPlayer $player) => $this->players[$player->getUniqueId()->toString()] = $player, $to->getPlayers());
		foreach ($this->players as $player)
		{
			$player->sendForm($this->getForm($player));
		}
	}

	public function decline(): void
	{
		parent::decline();
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
			}
		}
	}

	public function getPositionByTeam(Team $team): void
	{
		/*match ($team->getColor())
		{
			DyeColor::BLUE();
		}*/
	}

	public function getName(): string
    {
		return '2v2dual';
	}

    public function getChatFormat(): string
    {
		return "Duel 2vs2";
	}
}