<?php

namespace UnknowL\commands\money\sub;

use pocketmine\command\CommandSender;
use UnknowL\lib\commando\args\IntegerArgument;
use UnknowL\lib\commando\args\TargetArgument;
use UnknowL\lib\commando\BaseSubCommand;
use UnknowL\lib\commando\constraint\InGameRequiredConstraint;
use UnknowL\lib\commando\exception\ArgumentOrderException;
use UnknowL\Linesia;
use UnknowL\player\LinesiaPlayer;
use UnknowL\utils\CommandUtils;

final class MoneyPay extends BaseSubCommand
{

	public function __construct()
	{
		$settings = Linesia::getInstance()->getCommandManager()->getSettings("money")->getSubSettings("pay");
		parent::__construct($settings->getName(), $settings->getDescription(), $settings->getAliases());
	}

	/**
	 * @throws ArgumentOrderException
	 */
	protected function prepare(): void
	{
		$this->registerArgument(0, new TargetArgument("joueur"));
		$this->registerArgument(1, new IntegerArgument("montant"));
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
		if(count($args) === 2 )
		{
			if (
				(!is_null(($target = CommandUtils::checkTarget($args["joueur"]))))
				&&
				is_numeric(abs($args["montant"]))
			)
			{
				$sender->getEconomyManager()->transfer($args["montant"], $target);
				return;
			}
		}
		$this->sendUsage();
	}
}