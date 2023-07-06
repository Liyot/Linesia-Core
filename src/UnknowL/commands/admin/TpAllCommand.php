<?php

namespace UnknowL\commands\admin;

use pocketmine\command\CommandSender;
use pocketmine\player\Player;
use pocketmine\Server;
use UnknowL\lib\commando\BaseCommand;
use UnknowL\lib\commando\constraint\InGameRequiredConstraint;
use UnknowL\Linesia;
use UnknowL\player\LinesiaPlayer;

class TpAllCommand extends BaseCommand
{

    public function __construct()
    {
        $settings = Linesia::getInstance()->getCommandManager()->getSettings("tpall");
        parent::__construct(Linesia::getInstance(), $settings->getName(), $settings->getDescription(), $settings->getAliases());
    }

    protected function prepare(): void
    {
        $this->setPermission("pocketmine.group.op");
        $this->addConstraint(new InGameRequiredConstraint($this));
    }

    /**
     * @param LinesiaPlayer $sender
     */
    public function onRun(CommandSender $sender, string $aliasUsed, array $args): void
    {
        if($sender instanceof Player) {

            foreach (Server::getInstance()->getOnlinePlayers() as $p) {
                $p->teleport($sender->getPosition());
                $name = $sender->getName();
                $p->sendMessage("§aVous avez été téléporté sur $name");
            }
            $sender->sendMessage("§aVous avez bien téléporté tout le monde sur votre position");

        }
    }
}