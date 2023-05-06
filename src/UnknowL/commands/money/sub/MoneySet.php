<?php

namespace UnknowL\commands\money\sub;

use pocketmine\command\CommandSender;
use UnknowL\lib\commando\args\IntegerArgument;
use UnknowL\lib\commando\args\TargetArgument;
use UnknowL\lib\commando\BaseSubCommand;
use UnknowL\lib\commando\exception\ArgumentOrderException;
use UnknowL\Linesia;
use UnknowL\utils\CommandUtils;

final class MoneySet extends BaseSubCommand
{

	public function __construct()
	{
		$settings = Linesia::getInstance()->getCommandManager()->getSettings("money")->getSubSettings("set");
		parent::__construct($settings->getName(), $settings->getDescription(), $settings->getAliases());
	}

    /**
     * @inheritDoc
	 * @throws ArgumentOrderException
	 */
    protected function prepare(): void
    {
		$this->registerArgument(0, new TargetArgument("joueur"));
		$this->registerArgument(1, new IntegerArgument("montant"));
		$this->setPermission("money.set");
	}

    public function onRun(CommandSender $sender, string $aliasUsed, array $args): void
    {
		if($this->testPermissionSilent($sender))
		{
			if (count($args) === 2)
			{
				if(!is_null(($target = CommandUtils::checkTarget($args["joueur"]))))
				{
					$target->getEconomyManager()->set($args["montant"]);
				}
			}
		}
	}
}