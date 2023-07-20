<?php

namespace UnknowL\commands\stat;

use pocketmine\command\CommandSender;
use pocketmine\scheduler\AsyncTask;
use pocketmine\Server;
use UnknowL\handlers\Handler;
use UnknowL\handlers\OfflineDataHandler;
use UnknowL\lib\commando\BaseCommand;
use UnknowL\lib\commando\BaseSubCommand;
use UnknowL\Linesia;

class TopCommand extends BaseCommand
{

	public function __construct()
	{
		$settings = Linesia::getInstance()->getCommandManager()->getSettings('top');
		parent::__construct(Linesia::getInstance(), $settings->getName(), $settings->getDescription(), $settings->getAliases());
	}
    /**
     * @inheritDoc
     */
    protected function prepare(): void
    {
		$this->setPermission("pocketmine.group.user");
    }

    public function onRun(CommandSender $sender, string $aliasUsed, array $args): void
    {
	/*	$top = Handler::OFFLINEDATA()->getTopStats();
		$format = "Top: \n ";

		array_walk($top, fn(array $stat, string $key) =>  $format .= sprintf("%s: %s avec %s", $key, array_values($stat)[0], array_keys($stat)[0]));
		$sender->sendMessage($format);*/
	}
}