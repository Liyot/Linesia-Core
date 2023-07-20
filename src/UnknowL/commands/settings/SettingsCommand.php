<?php

namespace UnknowL\commands\settings;

use pocketmine\command\CommandSender;
use pocketmine\player\Player;
use UnknowL\form\SettingsForms;
use UnknowL\lib\commando\BaseCommand;
use UnknowL\lib\commando\constraint\InGameRequiredConstraint;
use UnknowL\Linesia;
use UnknowL\player\LinesiaPlayer;

class SettingsCommand extends BaseCommand
{

    public function __construct()
    {
        $settings = Linesia::getInstance()->getCommandManager()->getSettings("settings");
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
            SettingsForms::mainForm($sender);
        }
    }
}