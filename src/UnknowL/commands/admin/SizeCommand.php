<?php

namespace UnknowL\commands\admin;

use pocketmine\command\CommandSender;
use pocketmine\player\Player;
use UnknowL\lib\commando\BaseCommand;
use UnknowL\lib\commando\constraint\InGameRequiredConstraint;
use UnknowL\Linesia;
use UnknowL\player\LinesiaPlayer;

class SizeCommand extends BaseCommand
{

    public function __construct()
    {
        $settings = Linesia::getInstance()->getCommandManager()->getSettings("size");
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
            if (isset($args[0])) {
                if (preg_match('/^-?\d+(\.\d+)?$/', $args[0])) {
                    if ($args[0] > 0) {
                        if ($args[0] < 10) {
                            $sender->sendMessage("§aVotre taille à bien été mis en $args[0]");
                            $sender->setScale($args[0]);
                        } else $sender->sendMessage("§cLa valeur doit être inférieur à 10 !");
                    } else $sender->sendMessage("§cLa valeur doit être supérieur à 0 !");
                } else $sender->sendMessage("§cLa valeur doit être numéric !");
            } else $sender->sendMessage("§cVous devez indiquer un nombre !");
        }
    }
}