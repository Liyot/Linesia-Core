<?php

namespace UnknowL\blocks;

use pocketmine\item\Durable;
use pocketmine\item\Shovel;
use pocketmine\item\StringToItemParser;
use pocketmine\item\VanillaItems;
use pocketmine\Server;
use pocketmine\item\Armor;
use pocketmine\item\Tool;
use pocketmine\player\Player;
use UnknowL\player\LinesiaPlayer;
use UnknowL\player\manager\EconomyManager;
use jojoe77777\FormAPI\CustomForm;
use jojoe77777\FormAPI\SimpleForm;

class AnvilUI {

    public function Anvil($sender){
        $form = new SimpleForm(function(LinesiaPlayer $sender, $data){
            $result = $data;
            $item = $sender->getInventory()->getItemInHand();
            if($result === null){
                return;
            }
            switch($result){
                case 0:
                    if ($item instanceof Durable){
                        if ($sender->getEconomyManager()->getMoney() >= 100) {
							$item->setDamage(0);
                            $sender->getInventory()->setItemInHand($item);
							$sender->getEconomyManager()->reduce(100);
                        }else{
                            $sender->sendMessage("§cIl vous faut 100 coins pour réparer un item !");
                        }
                    }else{
                        $sender->sendMessage("§cVous devez avoir un item dans vos mains !");
                    }
                    break;
                case 1:
                    if ($sender->getInventory()->getItemInHand()->getTypeId() !== VanillaItems::AIR()->getTypeId()) {
                        if ($sender->getEconomyManager()->getMoney() >= 500) {
                            self::renameForm($sender);
                        } else $sender->sendMessage("§cIl vous faut 500 coins pour rename un item !");
                    } else $sender->sendMessage("§cVous devez avoir un item dans vos mains !");
                case 2:
                    //$sender->sendMessage("§aVous avez bien fermer l'interface de l'enclume.");
                    break;
            }
        });
        $form->setTitle("§d- §fEnclume §d-");
        $form->addButton("Réparer §e(100 coins)");
        $form->addButton("Rennomer §e(500 coins)");
        $form->addButton("§cFermer");
        $sender->sendForm($form);
    }

    public static function renameForm(Player $player)
    {
        $form = new CustomForm(function (LinesiaPlayer $player, array $data = null) {
            if ($data === null) {
                return true;
            }

            if ($player->getInventory()->getItemInHand()->getTypeId() !== VanillaItems::AIR()->getTypeId()) {
                if ($player->getEconomyManager()->getMoney() >= 500) {
                    if ($data[1] !== "") {
                        $player->getInventory()->setItemInHand($player->getInventory()->getItemInHand()->setCustomName($data[1]));
                        $player->getEconomyManager()->reduce(500);
                        $player->sendMessage("§aVous avez renommer l'item que vous avez dans les main en§2 $data[1] §a!");
                    } else $player->sendMessage("§cVous devez indiquer un nom !");
                } else $player->sendMessage("§cIl vous faut 500 coins !");
            } else $player->sendMessage("§cVous devez avoir un item dans vos mains !");
        });
        $form->setTitle("§d- §fEnclume §d-");
        $form->addLabel("§7Choisissez le nom que vous voulez donner à l'item dans votre main\n§7Il vous faut §e500 coins§7 !");
        $form->addInput("Nom");
        $form->sendToPlayer($player);
        return $form;
    }
}