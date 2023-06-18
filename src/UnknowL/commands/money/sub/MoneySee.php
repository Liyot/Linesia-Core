<?php

namespace UnknowL\commands\money\sub;

use pocketmine\command\CommandSender;
use UnknowL\lib\commando\args\TargetArgument;
use UnknowL\lib\commando\BaseSubCommand;
use UnknowL\lib\commando\constraint\InGameRequiredConstraint;
use UnknowL\lib\commando\exception\ArgumentOrderException;
use UnknowL\Linesia;
use UnknowL\player\LinesiaPlayer;
use UnknowL\utils\CommandUtils;

final class MoneySee extends BaseSubCommand
{

	public function __construct()
	{
		$settings = Linesia::getInstance()->getCommandManager()->getSettings("money")->getSubSettings("see");
		parent::__construct($settings->getName(), $settings->getDescription(), $settings->getAliases());
	}

	/**
	 * @throws ArgumentOrderException
	 */
	protected function prepare(): void
	{
		$this->setPermission("pocketmine.group.user");
		$this->registerArgument(0, new TargetArgument("joueur", true));
		$this->addConstraint(new InGameRequiredConstraint($this));
	}

	/**
	 * @param LinesiaPlayer $sender
	 * @param string $aliasUsed
	 * @param array $args
	 * @return void
	 */
	public function onRun(CommandSender $sender, string $aliasUsed, array $args): void
	{
		if(count($args) === 1)
		{
			if((!is_null($target = CommandUtils::checkTarget($args["joueur"]))))
			{
				$sender->sendMessage(sprintf("Le joueur %s possède %d $", $target->getName(), $target->getEconomyManager()->getMoney()));
			}
			return;
		}

		if(empty($args))
		{
			$sender->sendMessage(sprintf("Vous possédez %d $", $sender->getEconomyManager()->getMoney()));
		}
	}
}