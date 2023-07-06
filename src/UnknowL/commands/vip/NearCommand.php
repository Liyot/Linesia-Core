<?php

namespace UnknowL\commands\vip;

use pocketmine\command\CommandSender;
use pocketmine\player\Player;
use UnknowL\api\NearManager;
use UnknowL\lib\commando\BaseCommand;
use UnknowL\lib\commando\constraint\InGameRequiredConstraint;
use UnknowL\Linesia;
use UnknowL\player\LinesiaPlayer;

class NearCommand extends BaseCommand
{

    public array $cooldown = [];

    public function __construct()
    {
        $settings = Linesia::getInstance()->getCommandManager()->getSettings("near");
        parent::__construct(Linesia::getInstance(), $settings->getName(), $settings->getDescription(), $settings->getAliases());
    }

    protected function prepare(): void
    {
        $this->setPermission("near.use");
        $this->addConstraint(new InGameRequiredConstraint($this));
    }

    /**
     * @param LinesiaPlayer $sender
     */
    public function onRun(CommandSender $sender, string $aliasUsed, array $args): void
    {
        if($sender instanceof Player) {

            $playerName = $sender->getName();

            if(isset($this->cooldown[$playerName]) && $this->cooldown[$playerName] > time()) {

                $time = $this->cooldown[$playerName] - time();
                $sender->sendMessage("§cCette commande est en cooldown pendant $time s !");
                return;
            }

            NearManager::sendNear($sender);

            $this->cooldown[$sender->getName()] = time() + 60;

        }
    }
}