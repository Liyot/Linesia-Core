<?php

namespace UnknowL\commands\admin;

use pocketmine\command\CommandSender;
use pocketmine\player\Player;
use pocketmine\world\Position;
use UnknowL\lib\commando\BaseCommand;
use UnknowL\lib\commando\constraint\InGameRequiredConstraint;
use UnknowL\Linesia;
use UnknowL\player\LinesiaPlayer;

class SpyCommand extends BaseCommand
{

    public static array $spy = [];

    public function __construct()
    {
        $settings = Linesia::getInstance()->getCommandManager()->getSettings("spy");
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

            if (empty(self::$spy[$sender->getName()])) {
                self::$spy[$sender->getName()] = $sender->getName();
                $sender->sendMessage("§aVous êtes passé en mode espion !");
            } else {
                unset(self::$spy[$sender->getName()]);
                $sender->sendMessage("§cVous avez quitté le mode espion !");
            }

        }
    }
}