<?php

namespace UnknowL\commands\admin;

use pocketmine\command\CommandSender;
use pocketmine\player\Player;
use pocketmine\Server;
use UnknowL\lib\commando\BaseCommand;
use UnknowL\lib\commando\constraint\InGameRequiredConstraint;
use UnknowL\Linesia;
use UnknowL\player\LinesiaPlayer;

class SayCommand extends BaseCommand
{

    public function __construct()
    {
        $settings = Linesia::getInstance()->getCommandManager()->getSettings("say");
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
        if ($sender instanceof Player) {
            if (isset($args[0]) && isset($args[1])) {
                $type = strtolower($args[0]);
                $message = implode(" ", array_slice($args, 1));

                switch ($type) {
                    case "title":
                        Server::getInstance()->broadcastTitle("", $message);
                        break;
                    case "message":
                        Server::getInstance()->broadcastMessage("§5§lAnnonce » §r§d$message");
                        break;
                    default:
                        $sender->sendMessage("§cType de message invalide. Utilisation : /say (title|message) (message)");
                        break;
                }
            } else {
                $sender->sendMessage("§cUtilisation : /say (title|message) (message)");
            }
        }
    }
}