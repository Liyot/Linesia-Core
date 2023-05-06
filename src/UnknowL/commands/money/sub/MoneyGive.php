<?php

namespace UnknowL\commands\money\sub;

use pocketmine\command\CommandSender;
use UnknowL\lib\commando\args\IntegerArgument;
use UnknowL\lib\commando\args\TargetArgument;
use UnknowL\lib\commando\BaseSubCommand;
use UnknowL\Linesia;
use UnknowL\utils\CommandUtils;

final class MoneyGive extends BaseSubCommand
{

	public function __construct()
	{
		$settings = Linesia::getInstance()->getCommandManager()->getSettings("money")->getSubSettings("give");
		parent::__construct($settings->getName(), $settings->getDescription(), $settings->getAliases());
	}

    /**
     * @inheritDoc
     */
    protected function prepare(): void
    {
		$this->registerArgument(0, new TargetArgument("joueur"));
		$this->registerArgument(1, new IntegerArgument("montant"));
		$this->setPermission("money.give");
	}

	public function onRun(CommandSender $sender, string $aliasUsed, array $args): void
	{
		if($this->testPermissionSilent($sender))
		{
			if (count($args) === 2)
			{
				if(!is_null(($target = CommandUtils::checkTarget($args["joueur"]))))
				{
					$target->getEconomyManager()->add($args["montant"]);
				}
			}
		}
	}
}