<?php

namespace UnknowL\commands\warps;

use pocketmine\command\CommandSender;
use pocketmine\player\Player;
use pocketmine\world\Position;
use UnknowL\lib\commando\BaseCommand;
use UnknowL\lib\commando\constraint\InGameRequiredConstraint;
use UnknowL\Linesia;
use UnknowL\player\LinesiaPlayer;

class SpawnCommand extends BaseCommand
{

    public function __construct()
    {
        $settings = Linesia::getInstance()->getCommandManager()->getSettings("spawn");
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

            $sender->teleport(new Position(255, 70, 255, $sender->getServer()->getWorldManager()->getWorldByName("linesia")));
            $sender->sendMessage("§aTu as bien été téléporté au spawn !");

        }
    }
}