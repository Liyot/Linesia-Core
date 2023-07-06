<?php

namespace UnknowL\commands\admin;

use pocketmine\command\CommandSender;
use pocketmine\item\ItemBlock;
use pocketmine\player\Player;
use UnknowL\lib\commando\BaseCommand;
use UnknowL\lib\commando\constraint\InGameRequiredConstraint;
use UnknowL\Linesia;
use UnknowL\player\LinesiaPlayer;

class IdCommand extends BaseCommand
{

    public function __construct()
    {
        $settings = Linesia::getInstance()->getCommandManager()->getSettings("id");
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

                $item = $sender->getInventory()->getItemInHand();

                if ($item->getTypeId() !== 0) {

                    if ($item instanceof ItemBlock) {

                        $iditem = $item->getTypeId();
                        $damage = $item->getStateId();
                        $idblock = $item->getBlock()->getTypeId();
                        $name = $item->getVanillaName();

                        $sender->sendMessage("§2Id de l'item: §a$iditem:$damage \n §2Nom de l'item : §a$name \n §2Id du block : §a$idblock");
                    } else {

                        $iditem = $item->getTypeId();
                        $damage = $item->getStateId();
                        $name = $item->getVanillaName();

                        $sender->sendMessage("§2Id de l'item: §a$iditem:$damage \n §2Nom de l'item : §a$name");
                }
            }
        }
    }
}