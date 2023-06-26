<?php

namespace UnknowL\commands\rank\sub;

use pocketmine\command\CommandSender;
use pocketmine\Server;
use UnknowL\handlers\dataTypes\PlayerCooldown;
use UnknowL\handlers\Handler;
use UnknowL\lib\commando\args\StringArgument;
use UnknowL\lib\commando\args\TargetArgument;
use UnknowL\Linesia;
use UnknowL\utils\CommandUtils;
use UnknowL\utils\PathLoader;

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
        $this->registerArgument(2, new StringArgument("cooldown (day)", true));
	}

    public function onRun(CommandSender $sender, string $aliasUsed, array $args): void
    {
		if(count($args) >= 2)
		{
			if (!is_null(($rank = Handler::RANK()->getRank($args["rank"]))))
			{
				if(($player = CommandUtils::checkTarget($args["joueur"])))
				{
					foreach ($rank->getPermissions() as $permission)
					{
						$player->addPermission($permission);
					}
                    if (isset($args[2]) && $args[2] > 0)
                    {
                        $path = PathLoader::PATH_RANK_CACHE;
                        $cooldown = new PlayerCooldown(\DateTime::createFromFormat("d:H:i:s", "0:0:0:0")->setDate(0, $args[2], 0), $player, $path);
                        $player->addCooldown($cooldown, $path);
                    }
					$sender->sendMessage("Commande effectué avec succés");
					return;
				}
			}
		}
		$sender->sendMessage($this->getUsageMessage());
    }
}