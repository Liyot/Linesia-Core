<?php

namespace UnknowL\commands\vip;

use pocketmine\command\CommandSender;
use pocketmine\player\Player;
use UnknowL\lib\commando\BaseCommand;
use UnknowL\lib\commando\constraint\InGameRequiredConstraint;
use UnknowL\Linesia;
use UnknowL\player\LinesiaPlayer;

class RenameCommand extends BaseCommand
{

    private $blockedWords = ["hitler", "ez", "ezz", "ezzz", "enculer", "nul", "fdp", "pute", "tg", "ntm", "grisollet", "remy", "ftg", "gueule", "bordel", "putain", "merde", "con", "connard", "batard", "cul", "bite", "couille", "clc", "csc", "enfoiré", "enfoire", "petasse", "abruti", "bouffon", "veski"];

    public function __construct()
    {
        $settings = Linesia::getInstance()->getCommandManager()->getSettings("rename");
        parent::__construct(Linesia::getInstance(), $settings->getName(), $settings->getDescription(), $settings->getAliases());
    }

    protected function prepare(): void
    {
        $this->setPermission("rename.use");
        $this->addConstraint(new InGameRequiredConstraint($this));
    }

    /**
     * @param LinesiaPlayer $sender
     */
    public function onRun(CommandSender $sender, string $aliasUsed, array $args): void
    {
        if($sender instanceof Player) {

            $item = $sender->getInventory()->getItemInHand();

            if($item->getTypeId() !== 0){

                if(!isset($args[0])) {
                    $sender->sendMessage("§cUsage: /rename (nom)");

                }else {

                    $name = $args[0];
                    if (preg_match('/§[0-9a-fk-or]/i', $name)) {
                        $sender->sendMessage("§cLes couleurs sont interdites !");
                        return;
                    }

                    if (strlen($name) > 15) {
                        $sender->sendMessage("§cVeuillez spécifier moins de 15 caractères !");
                        return;
                    }

                    foreach ($this->blockedWords as $word) {
                        if (stripos($name, $word) !== false) {
                            $sender->sendMessage("§cLe mot '$word' est interdit !");
                            break;
                        }
                    }

                    $item->setCustomName($args[0]);

                    $sender->getInventory()->setItemInHand($item);
                    $sender->sendMessage("§aVotre item a bien été renommé.");
                }
            }else{
                $sender->sendMessage("§cVous devez avoir un item dans votre main.");
            }

        }
    }
}