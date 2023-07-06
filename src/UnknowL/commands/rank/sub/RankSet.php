<?php

namespace UnknowL\commands\rank\sub;

use pocketmine\command\CommandSender;
use pocketmine\Server;
use UnknowL\api\ScoreBoardAPI;
use UnknowL\handlers\Handler;
use UnknowL\lib\commando\args\StringArgument;
use UnknowL\lib\commando\args\TargetArgument;
use UnknowL\lib\commando\BaseSubCommand;
use UnknowL\lib\commando\constraint\ConsoleRequiredConstraint;
use UnknowL\lib\commando\constraint\InGameRequiredConstraint;
use UnknowL\Linesia;
use UnknowL\utils\CommandUtils;

final class RankSet extends BaseSubCommand
{

	public function __construct()
	{
		$settings = Linesia::getInstance()->getCommandManager()->getSettings("rank")->getSubSettings("set");
		parent::__construct($settings->getName(), $settings->getDescription(), $settings->getAliases());
	}

	protected function prepare(): void
	{
		$this->registerArgument(0, new TargetArgument("joueur"));
		$this->registerArgument(1, new StringArgument("rank"));
		$this->setPermission("rank.set");
	}

	public function onRun(CommandSender $sender, string $aliasUsed, array $args): void
	{
		if(count($args) === 2)
		{
			if($this->testPermissionSilent($sender) && ($player = CommandUtils::checkTarget($args["joueur"])))
			{
				$rank = Handler::RANK()->getRank($args["rank"]);
				is_null($rank) ? $sender->sendMessage("Ce grade n'éxiste pas") : $player->setRank($rank);

                $co = Server::getInstance()->getPlayerExact($player);
                if ($co !== null) {
                    ScoreBoardAPI::updateRank($player);
                }
			}
			return;
		}
		$this->sendUsage();
	}
}