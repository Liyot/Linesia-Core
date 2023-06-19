<?php

namespace UnknowL\commands\rank\sub;

use pocketmine\command\CommandSender;
use pocketmine\Server;
use UnknowL\handlers\Handler;
use UnknowL\lib\commando\args\StringArgument;
use UnknowL\lib\commando\args\TargetArgument;
use UnknowL\Linesia;
use UnknowL\utils\CommandUtils;

class RankSetCache extends \UnknowL\lib\commando\BaseSubCommand
{

	public function __construct()
	{
		$settings = Linesia::getInstance()->getCommandManager()->getSettings("rank")->getSubSettings("setcache");
		parent::__construct($settings->getName(), $settings->getDescription(), $settings->getAliases());
	}


	/**
     * @inheritDoc
     */
    protected function prepare(): void
    {
		$this->setPermission("pocketmine.group.user");
		$this->registerArgument(0, new TargetArgument("player"));
		$this->registerArgument(1, new StringArgument("rank"));
	}

    public function onRun(CommandSender $sender, string $aliasUsed, array $args): void
    {
		if(count($args) === 2)
		{
			if (!is_null(($rank = Handler::RANK()->getRank($args["rank"]))))
			{
				if(($player = CommandUtils::checkTarget($args["joueur"])))
				{
					foreach ($rank->getPermissions() as $permission)
					{
						$player->addPermission($permission);
					}
					$sender->sendMessage("Commande effectué avec succés");
					return;
				}
			}
		}
		$sender->sendMessage($this->getUsageMessage());
    }
}