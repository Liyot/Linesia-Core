<?php

namespace UnknowL\commands\money;

use pocketmine\command\CommandSender;
use UnknowL\commands\money\sub\MoneyGive;
use UnknowL\commands\money\sub\MoneyPay;
use UnknowL\commands\money\sub\MoneyRemove;
use UnknowL\commands\money\sub\MoneySee;
use UnknowL\commands\money\sub\MoneySet;
use UnknowL\lib\commando\BaseCommand;

class MoneyCommand extends BaseCommand
{

    /**
     * @inheritDoc
     */
    protected function prepare(): void
    {
		$this->registerSubCommand(new MoneyPay());
		$this->registerSubCommand(new MoneyGive());
		$this->registerSubCommand(new MoneySet());
		$this->registerSubCommand(new MoneyRemove());
		$this->registerSubCommand(new MoneySee());
	}

    /**
     * @inheritDoc
     */
    public function onRun(CommandSender $sender, string $aliasUsed, array $args): void
    {
		//TODO: Page d'aide
    }
}