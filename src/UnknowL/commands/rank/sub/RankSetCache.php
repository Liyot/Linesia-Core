<?php

namespace UnknowL\commands\rank\sub;

use pocketmine\command\CommandSender;
use pocketmine\Server;
use UnknowL\handlers\dataTypes\PlayerCooldown;
use UnknowL\handlers\Handler;
use UnknowL\lib\commando\args\IntegerArgument;
use UnknowL\lib\commando\args\RankArgument;
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
		$this->setPermission("pocketmine.group.op");
		$this->registerArgument(0, new TargetArgument("player"));
		$this->registerArgument(1, new RankArgument("rank"));
        $this->registerArgument(2, new IntegerArgument("cooldown (day)", true));
	}

    public function onRun(CommandSender $sender, string $aliasUsed, array $args): void
    {
		if(count($args) >= 2)
		{
			if (!is_null(($rank = $args["rank"])))
			{
				if(($player = CommandUtils::checkTarget($args["player"])))
				{
					$time = 0;
                    if (isset($args[2]) && $args[2] > 0)
                    {
                        $path = PathLoader::PATH_RANK_CACHE;
                        $cooldown = new PlayerCooldown($args[2] * 86400, $player, $path);
                        $player->addCooldown($cooldown, $path);
						$time = time() + ($args[2] * 86400);
                    }

					foreach ($rank->getPermissions() as $permission)
					{
						$player->addPermission($permission, $time);
					}
					$sender->sendMessage("Commande effectué avec succés");
					return;
				}
				$sender->sendMessage('Le joueur est introuvable');
			}
		}
		$sender->sendMessage($this->getUsageMessage());
    }
}