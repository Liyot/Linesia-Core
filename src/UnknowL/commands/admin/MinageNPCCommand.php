<?php

namespace UnknowL\commands\admin;

use pocketmine\command\CommandSender;
use pocketmine\player\Player;
use UnknowL\entities\MinageNPC;
use UnknowL\lib\commando\BaseCommand;
use UnknowL\lib\commando\constraint\InGameRequiredConstraint;
use UnknowL\Linesia;
use UnknowL\player\LinesiaPlayer;

class MinageNPCCommand extends BaseCommand
{

    public function __construct()
    {
        $settings = Linesia::getInstance()->getCommandManager()->getSettings("minagenpc");
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

            $entity = new MinageNPC($sender->getLocation(), null);
            $entity->setScoreTag("Gerard");
            $entity->spawnToAll();
        }
    }
}