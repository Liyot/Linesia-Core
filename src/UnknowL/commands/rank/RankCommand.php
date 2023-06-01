<?php

namespace UnknowL\commands\rank;

use pocketmine\command\CommandSender;
use pocketmine\plugin\Plugin;
use UnknowL\commands\rank\sub\RankAddPerm;
use UnknowL\lib\commando\BaseCommand;
use UnknowL\Linesia;

final class RankCommand extends BaseCommand
{

	public function __construct()
	{
		$settings = Linesia::getInstance()->getCommandManager()->getSettings("rank");
		parent::__construct(Linesia::getInstance(), $settings->getName(), $settings->getDescription(), $settings->getAliases());
	}

	protected function prepare(): void
	{
		$this->registerSubCommand(new RankAddPerm());
	}

	public function onRun(CommandSender $sender, string $aliasUsed, array $args): void
	{
	}
}