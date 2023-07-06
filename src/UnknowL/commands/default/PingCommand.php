<?php

namespace UnknowL\commands\default;

use pocketmine\command\CommandSender;
use pocketmine\player\Player;
use pocketmine\Server;
use UnknowL\lib\commando\BaseCommand;
use UnknowL\lib\commando\constraint\InGameRequiredConstraint;
use UnknowL\Linesia;
use UnknowL\player\LinesiaPlayer;

class PingCommand extends BaseCommand
{

    public function __construct()
    {
        $settings = Linesia::getInstance()->getCommandManager()->getSettings("ping");
        parent::__construct(Linesia::getInstance(), $settings->getName(), $settings->getDescription(), $settings->getAliases());
    }

    protected function prepare(): void
    {
        $this->setPermission("pocketmine.group.user");
        $this->addConstraint(new InGameRequiredConstraint($this));
    }

    /**
     * @param LinesiaPlayer $sender
     */
    public function onRun(CommandSender $sender, string $aliasUsed, array $args): void
    {
        if($sender instanceof Player) {

            if(isset($args[0])) {
                $player = Server::getInstance()->getPlayerExact($args[0]);
                $ping = $player->getNetworkSession()->getPing();
                $name = $args[0];
                if($player) {
                    $sender->sendMessage("§aLe ping de §2$name §aest de §2$ping ms§a.");
                } else {
                    $sender->sendMessage("§cL'utilisateur §4$name §cn'est pas en ligne.");
                }
            } else {
                $SelfPing = $sender->getNetworkSession()->getPing();
                $sender->sendMessage("§aVotre ping est de §2$SelfPing ms§a.");
            }
        }
    }
}