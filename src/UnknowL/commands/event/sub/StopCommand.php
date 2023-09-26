<?php

namespace UnknowL\commands\event\sub;

use pocketmine\command\CommandSender;
use UnknowL\commands\event\args\GameArgument;
use UnknowL\games\BaseGame;
use UnknowL\lib\commando\BaseSubCommand;
use UnknowL\lib\commando\constraint\InGameRequiredConstraint;
use UnknowL\Linesia;
use UnknowL\player\LinesiaPlayer;

final class StopCommand extends BaseSubCommand
{

	public function __construct()
	{
		$settings = Linesia::getInstance()->getCommandManager()->getSettings("adminevent")->getSubSettings('start');
		parent::__construct($settings->getName(), $settings->getDescription(), $settings->getAliases());
	}

	/**
	 * @inheritDoc
	 */
	protected function prepare(): void
	{
		$this->setPermission('admin.event.command');
		$this->addConstraint(new InGameRequiredConstraint($this));
		$this->registerArgument(0, new GameArgument("game"));
	}

	/**
	 * @inheritDoc
	 */
	public function onRun(CommandSender $sender, string $aliasUsed, array $args): void
	{
		if ($sender instanceof LinesiaPlayer)
		{
			if (isset($args[0]))
			{
				/**@var BaseGame $game*/
				$game = $args[0];
				if ($game->hasStarted())
				{
					$game->stop();
					$sender->sendMessage("Vous avez bien stoppé le ". $game->getName());
				}
			}
		}
	}
}