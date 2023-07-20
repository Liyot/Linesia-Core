<?php

namespace UnknowL\commands\vip;

use pocketmine\command\CommandSender;
use pocketmine\player\Player;
use UnknowL\form\EnderChestForm;
use UnknowL\lib\commando\BaseCommand;
use UnknowL\lib\commando\constraint\InGameRequiredConstraint;
use UnknowL\Linesia;
use UnknowL\player\LinesiaPlayer;

class EcCommand extends BaseCommand
{

    public function __construct()
    {
        $settings = Linesia::getInstance()->getCommandManager()->getSettings("ec");
        parent::__construct(Linesia::getInstance(), $settings->getName(), $settings->getDescription(), $settings->getAliases());
    }

    protected function prepare(): void
    {
        $this->setPermission("ec.use");
        $this->addConstraint(new InGameRequiredConstraint($this));
    }

    /**
     * @param LinesiaPlayer $sender
     */
    public function onRun(CommandSender $sender, string $aliasUsed, array $args): void
    {
        if($sender instanceof Player) {

            $ec = new EnderChestForm($sender);
            $ec->open();
        }
    }
}