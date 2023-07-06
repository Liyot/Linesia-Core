<?php

namespace UnknowL\blocks;

use pocketmine\item\Tool;
use pocketmine\item\VanillaItems;
use pocketmine\Server;
use pocketmine\player\Player;
use pocketmine\item\Armor;
use pocketmine\item\Axe;
use pocketmine\item\enchantment\VanillaEnchantments;
use pocketmine\item\Pickaxe;
use pocketmine\item\Shovel;
use pocketmine\item\Sword;
use pocketmine\item\enchantment\EnchantmentInstance;
use UnknowL\player\LinesiaPlayer;
use UnknowL\player\manager\EconomyManager;
use jojoe77777\FormAPI\CustomForm;
use jojoe77777\FormAPI\SimpleForm;

class EnchantUI {

    public function enchantementUi($sender)
    {
        $form = new SimpleForm(function (Player $sender, $data) {
            $result = $data;
            $item = $sender->getInventory()->getItemInHand();
            if ($result === null) {
                return true;
            }
            switch ($result) {
                case 0:
                    break;
                case 1:
                    if ($item instanceof Armor) {
                        $this->protectionUi($sender);
                    } else {
                        $sender->sendMessage("§cVous devez tenir une pièce d'armure dans vos main.");
                    }
                    break;
                case 2:
                    if ($item instanceof Armor or $item instanceof Pickaxe or $item instanceof Shovel or $item instanceof Axe or $item instanceof Sword or $item instanceof Tool) {
                        $this->soliditeUi($sender);
                    } else {
                        $sender->sendMessage("§cVous ne pouvez pas enchante cette item.");
                    }
                    break;
                case 3:
                    if ($item instanceof Sword) {
                        $this->sharpnessUi($sender);
                    } else {
                        $sender->sendMessage("§cVous devez tenir une épée dans vos main.");
                    }
                    break;
                case 4:
                    if ($item->getTypeID() == VanillaItems::BOW()->getTypeId()) {
                        $this->PunchUi($sender);
                    } else {
                        $sender->sendMessage("§cVous devez tenir un arc dans vos main.");
                    }
                    break;
                case 5:
                    if ($item instanceof Pickaxe or $item instanceof Shovel or $item instanceof Axe) {
                        $this->efficaciteUi($sender);
                    } else {
                        $sender->sendMessage("§cVous devez tenir un outils dans vos main.");
                    }
                    break;
                case 6:
                    if ($item instanceof Pickaxe or $item instanceof Shovel or $item instanceof Axe) {
                        $this->silktuchUi($sender);
                    } else {
                        $sender->sendMessage("§cVous devez tenir un outils dans vos main.");
                    }
                    break;
            }
        });
        $form->setTitle("Enchantement");
        $form->setContent("§7Veuillez séléctionner une catégorie d'enchantement :");
        $form->addButton("§cFermer");
        $form->addButton("Protection");
        $form->addButton("Solidité");
        $form->addButton("Tranchant");
        $form->addButton("Punch");
        $form->addButton("Efficacité");
        $form->addButton("Délicatesse");
        $form->sendToPlayer($sender);
    }

    public function protectionUi($sender)
    {
        $form = new SimpleForm(function (LinesiaPlayer $sender, $data) {
            $item = $sender->getInventory()->getItemInHand();
            $result = $data;
            if ($result === null) {
                return true;
            }
            switch ($result) {
                case 0:
                    break;
                case 1:
                    if ($sender->getEconomyManager()->getMoney() >= 100) {
                        $item->addEnchantment(new EnchantmentInstance(VanillaEnchantments::PROTECTION(), 1));
                        $sender->getInventory()->setItemInHand($item);
                        $sender->getEconomyManager()->reduce(100);
                        $sender->sendMessage("§aVotre item a bien été enchanté avec l'enchantement §dProtection I §a!");
                    } else {
                        $sender->sendMessage("§cVous devez possèder §e100$ §cpour effectuer cette action.");
                    }
                    break;
                case 2:
                    if ($sender->getEconomyManager()->getMoney() >= 200) {
                        $item->addEnchantment(new EnchantmentInstance(VanillaEnchantments::PROTECTION(), 2));
                        $sender->getInventory()->setItemInHand($item);
                        $sender->getEconomyManager()->reduce(200);
                        $sender->sendMessage("§d§l»§r§f Votre item a bien été enchanté avec l'enchantement §dProtection II §f!");
                    } else {
                        $sender->sendMessage("§d§l»§r§f Vous devez possèder §d200$ §fpour effectuer cette action.");
                    }
                    break;
                case 3:
                    if ($sender->getEconomyManager()->getMoney() >= 400) {
                        $item->addEnchantment(new EnchantmentInstance(VanillaEnchantments::PROTECTION(), 3));
                        $sender->getInventory()->setItemInHand($item);
                        $sender->getEconomyManager()->reduce(400);
                        $sender->sendMessage("§aVotre item a bien été enchanté avec l'enchantement §dProtection III §a!");
                    } else {
                        $sender->sendMessage("§cVous devez possèder §e400$ §cpour effectuer cette action.");
                    }
                    break;
                case 4:
                    if ($sender->getEconomyManager()->getMoney() >= 700) {
                        $item->addEnchantment(new EnchantmentInstance(VanillaEnchantments::PROTECTION(), 4));
                        $sender->getInventory()->setItemInHand($item);
                        $sender->getEconomyManager()->reduce(700);
                        $sender->sendMessage("§aVotre item a bien été enchanté avec l'enchantement §dProtection IV §a!");
                    } else {
                        $sender->sendMessage("§cVous devez possèder §e700$ §cpour effectuer cette action.");
                    }
                    break;
            }
        });
        $name = $sender->getName();
        $form->setTitle("Enchantement");
        $form->setContent("§7Veuillez séléctionner un enchantement :");
        $form->addButton("§cFermer");
        $form->addButton("Protection I\n§e100$");
        $form->addButton("Protection II\n§e200$");
        $form->addButton("Protection III\n§e400$");
        $form->addButton("Protection IV\n§e700$");
        $form->sendToPlayer($sender);
    }
    
        public function sharpnessUi($sender)
    {
        $form = new SimpleForm(function (LinesiaPlayer $sender, $data) {
            $item = $sender->getInventory()->getItemInHand();
            $result = $data;
            if ($result === null) {
                return true;
            }
            switch ($result) {
                case 0:
                    break;
                case 1:
                    if ($sender->getEconomyManager()->getMoney() >= 200) {
                        $item->addEnchantment(new EnchantmentInstance(VanillaEnchantments::SHARPNESS(), 1));
                        $sender->getInventory()->setItemInHand($item);
                        $sender->getEconomyManager()->reduce(200);
                        $sender->sendMessage("§aVotre item a bien été enchanté avec l'enchantement §dTranchant I §a!");
                    } else {
                        $sender->sendMessage("§cVous devez possèder §e200$ §cpour effectuer cette action.");
                    }
                    break;
                case 2:
                    if ($sender->getEconomyManager()->getMoney() >= 400) {
                        $item->addEnchantment(new EnchantmentInstance(VanillaEnchantments::SHARPNESS(), 2));
                        $sender->getInventory()->setItemInHand($item);
                        $sender->getEconomyManager()->reduce(400);
                        $sender->sendMessage("§aVotre item a bien été enchanté avec l'enchantement §dTranchant II §a!");
                    } else {
                        $sender->sendMessage("§cVous devez possèder §e400$ §cpour effectuer cette action.");
                    }
                    break;
                case 3:
                    if ($sender->getEconomyManager()->getMoney() >= 700) {
                        $item->addEnchantment(new EnchantmentInstance(VanillaEnchantments::SHARPNESS(), 3));
                        $sender->getInventory()->setItemInHand($item);
                        $sender->getEconomyManager()->reduce(700);
                        $sender->sendMessage("§aVotre item a bien été enchanté avec l'enchantement §dTranchant III §a!");
                    } else {
                        $sender->sendMessage("§cVous devez possèder §e700$ §cpour effectuer cette action.");
                    }
                    break;
                case 4:
                    if ($sender->getEconomyManager()->getMoney() >= 1000) {
                        $item->addEnchantment(new EnchantmentInstance(VanillaEnchantments::SHARPNESS(), 4));
                        $sender->getInventory()->setItemInHand($item);
                        $sender->getEconomyManager()->reduce(1000);
                        $sender->sendMessage("§aVotre item a bien été enchanté avec l'enchantement §dTranchant IV §a!");
                    } else {
                        $sender->sendMessage("§cVous devez possèder §e1000$ §cpour effectuer cette action.");
                    }
                    break;

            }
        });
        $name = $sender->getName();
        $form->setTitle("Enchantement");
        $form->setContent("§7Veuillez séléctionner un enchantement :");
        $form->addButton("§cFermer");
        $form->addButton("Tranchant I\n§e200$");
        $form->addButton("Tranchant II\n§e400$");
        $form->addButton("Tranchant III\n§e700$");
        $form->addButton("Tranchant IV\n§e1000$");
        $form->sendToPlayer($sender);
    }

    public function soliditeUi($sender)
    {
        $form = new SimpleForm(function (LinesiaPlayer $sender, $data) {
            $item = $sender->getInventory()->getItemInHand();
            $result = $data;
            if ($result === null) {
                return true;
            }
            switch ($result) {
                case 0:
                    break;
                case 1:
                    if ($sender->getEconomyManager()->getMoney() >= 100) {
                        $item->addEnchantment(new EnchantmentInstance(VanillaEnchantments::UNBREAKING(), 1));
                        $sender->getInventory()->setItemInHand($item);
                        $sender->getEconomyManager()->reduce(100);
                        $sender->sendMessage("§aVotre item a bien été enchanté avec l'enchantement §dSolidité I §a!");
                    } else {
                        $sender->sendMessage("§cVous devez possèder §e100$ §cpour effectuer cette action.");
                    }
                    break;
                case 2:
                    if ($sender->getEconomyManager()->getMoney() >= 200) {
                        $item->addEnchantment(new EnchantmentInstance(VanillaEnchantments::UNBREAKING(), 2));
                        $sender->getInventory()->setItemInHand($item);
                        $sender->getEconomyManager()->reduce(200);
                        $sender->sendMessage("§aVotre item a bien été enchanté avec l'enchantement §dSolidité II §a!");
                    } else {
                        $sender->sendMessage("§cVous devez possèder §e200$ §cpour effectuer cette action.");
                    }
                    break;
                case 3:
                    if ($sender->getEconomyManager()->getMoney() >= 500) {
                        $item->addEnchantment(new EnchantmentInstance(VanillaEnchantments::UNBREAKING(), 3));
                        $sender->getInventory()->setItemInHand($item);
                        $sender->getEconomyManager()->reduce(500);
                        $sender->sendMessage("§aVotre item a bien été enchanté avec l'enchantement §dSolidité III §a!");
                    } else {
                        $sender->sendMessage("§cVous devez possèder §e500$ §cpour effectuer cette action.");
                    }
                    break;
            }
        });
        $name = $sender->getName();
        $form->setTitle("Enchantement");
        $form->setContent("§7Veuillez séléctionner un enchantement :");
        $form->addButton("§cFermer");
        $form->addButton("Soliditié I\n§e100 $");
        $form->addButton("Solditié II\n§e200 $");
        $form->addButton("Solditié III\n§e500 $");
        $form->sendToPlayer($sender);
    }

    public function infinityUi($sender)
    {
        $form = new SimpleForm(function (LinesiaPlayer $sender, $data) {
            $item = $sender->getInventory()->getItemInHand();
            $result = $data;
            if ($result === null) {
                return true;
            }
            switch ($result) {
                case 0:
                    break;
                case 1:
                    if ($sender->getEconomyManager()->getMoney() >= 1000) {
                        $item->addEnchantment(new EnchantmentInstance(VanillaEnchantments::INFINITY(), 1));
                        $sender->getInventory()->setItemInHand($item);
                        $sender->getEconomyManager()->reduce(1000);
                        $sender->sendMessage("§aVotre item a bien été enchanté avec l'enchantement §dInfinité §a!");
                    } else {
                        $sender->sendMessage("§cVous devez possèder §e1000$ §cpour effectuer cette action.");
                    }
                    break;
            }
        });
        $name = $sender->getName();
        $form->setTitle("Enchantement");
        $form->setContent("§7Veuillez séléctionner un enchantement :");
        $form->addButton("§cFermer");
        $form->addButton("Infinité\n§e1000 $");
        $form->sendToPlayer($sender);
    }

    public function silktuchUi($sender)
    {
        $form = new SimpleForm(function (LinesiaPlayer $sender, $data) {
            $item = $sender->getInventory()->getItemInHand();
            $result = $data;
            if ($result === null) {
                return true;
            }
            switch ($result) {
                case 0:
                    break;
                case 1:
                    if ($sender->getEconomyManager()->getMoney() >= 1000) {
                        $item->addEnchantment(new EnchantmentInstance(VanillaEnchantments::SILK_TOUCH(), 1));
                        $sender->getInventory()->setItemInHand($item);
                        $sender->getEconomyManager()->reduce(1000);
                        $sender->sendMessage("§aVotre item a bien été enchanté avec l'enchantement §dDélicatesse §a!");
                    } else {
                        $sender->sendMessage("§cVous devez possèder §e1000$ §cpour effectuer cette action.");
                    }
                    break;
            }
        });
        $name = $sender->getName();
        $form->setTitle("Enchantement");
        $form->setContent("§7Veuillez séléctionner un enchantement :");
        $form->addButton("§cFermer");
        $form->addButton("Délicatesse\n§e1000 $");
        $form->sendToPlayer($sender);
    }


    public function PunchUi($sender)
    {
        $form = new SimpleForm(function (LinesiaPlayer $sender, $data) {
            $item = $sender->getInventory()->getItemInHand();
            $result = $data;
            if ($result === null) {
                return true;
            }
            switch ($result) {
                case 0:
                    break;
                case 1:
                    if ($sender->getEconomyManager()->getMoney() >= 1000) {
                        $item->addEnchantment(new EnchantmentInstance(VanillaEnchantments::PUNCH(), 1));
                        $sender->getInventory()->setItemInHand($item);
                        $sender->getEconomyManager()->reduce(1000);
                        $sender->sendMessage("§aVotre item a bien été enchanté avec l'enchantement §dPunch I §a!");
                    } else {
                        $sender->sendMessage("§cVous devez possèder §e1000$ §cpour effectuer cette action.");
                    }
                    break;
                case 2:
                    if ($sender->getEconomyManager()->getMoney() >= 2500) {
                        $item->addEnchantment(new EnchantmentInstance(VanillaEnchantments::PUNCH(), 2));
                        $sender->getInventory()->setItemInHand($item);
                        $sender->getEconomyManager()->reduce(2500);
                        $sender->sendMessage("§aVotre item a bien été enchanté avec l'enchantement §dPunch II §a!");
                    } else {
                        $sender->sendMessage("§cVous devez possèder §e2500$ §cpour effectuer cette action.");
                    }
                    break;
            }
        });
        $name = $sender->getName();
        $form->setTitle("Enchantement");
        $form->setContent("§7Veuillez séléctionner un enchantement :");
        $form->addButton("§cFermer");
        $form->addButton("Punch I\n§e1000 $");
        $form->addButton("Punch II\n§e2500 $");
        $form->sendToPlayer($sender);
    }

    public function efficaciteUi($sender)
    {
        $form = new SimpleForm(function (LinesiaPlayer $sender, $data) {
            $item = $sender->getInventory()->getItemInHand();
            $result = $data;
            if ($result === null) {
                return true;
            }
            switch ($result) {
                case 0:
                    $this->enchantementUi($sender);
                    break;
                case 1:
                    if ($sender->getEconomyManager()->getMoney() >= 100) {
                        $item->addEnchantment(new EnchantmentInstance(VanillaEnchantments::EFFICIENCY(), 1));
                        $sender->getInventory()->setItemInHand($item);
                        $sender->getEconomyManager()->reduce(100);
                        $sender->sendMessage("§aVotre item a bien été enchanté avec l'enchantement §dEfficacité I §a!");
                    } else {
                        $sender->sendMessage("§cVous devez possèder §e100$ §cpour effectuer cette action.");
                    }
                    break;
                case 2:
                    if ($sender->getEconomyManager()->getMoney() >= 200) {
                        $item->addEnchantment(new EnchantmentInstance(VanillaEnchantments::EFFICIENCY(), 2));
                        $sender->getInventory()->setItemInHand($item);
                        $sender->getEconomyManager()->reduce(200);
                        $sender->sendMessage("§aVotre item a bien été enchanté avec l'enchantement §dEfficacité II §a!");
                    } else {
                        $sender->sendMessage("§cVous devez possèder §e200$ §cpour effectuer cette action.");
                    }
                    break;
                case 3:
                    if ($sender->getEconomyManager()->getMoney() >= 400) {
                        $item->addEnchantment(new EnchantmentInstance(VanillaEnchantments::EFFICIENCY(), 3));
                        $sender->getInventory()->setItemInHand($item);
                        $sender->getEconomyManager()->reduce(400);
                        $sender->sendMessage("§aVotre item a bien été enchanté avec l'enchantement §dEfficacité III §a!");
                    } else {
                        $sender->sendMessage("§cVous devez possèder §e400$ §cpour effectuer cette action.");
                    }
                    break;
                case 4:
                    if ($sender->getEconomyManager()->getMoney() >= 800) {
                        $item->addEnchantment(new EnchantmentInstance(VanillaEnchantments::EFFICIENCY(), 4));
                        $sender->getInventory()->setItemInHand($item);
                        $sender->getEconomyManager()->reduce(800);
                        $sender->sendMessage("§aVotre item a bien été enchanté avec l'enchantement §dEfficacité IV §a!");
                    } else {
                        $sender->sendMessage("§cVous devez possèder §e800$ §cpour effectuer cette action.");
                    }
                    break;
                case 5:
                    if ($sender->getEconomyManager()->getMoney() >= 1000) {
                        $item->addEnchantment(new EnchantmentInstance(VanillaEnchantments::EFFICIENCY(), 5));
                        $sender->getInventory()->setItemInHand($item);
                        $sender->getEconomyManager()->reduce(1000);
                        $sender->sendMessage("§aVotre item a bien été enchanté avec l'enchantement §dEfficacité V §a!");
                    } else {
                        $sender->sendMessage("§cVous devez possèder §e1000$ §cpour effectuer cette action.");
                    }
                    break;
            }
        });
        $name = $sender->getName();
        $form->setTitle("Enchantement");
        $form->setContent("§7Veuillez séléctionner un enchantement :");
        $form->addButton("§cFermer");
        $form->addButton("Efficacité I\n§e100 $");
        $form->addButton("Efficacité II\n§e200 $");
        $form->addButton("Efficacité III\n§e400 $");
        $form->addButton("Efficacité IV\n§e800 $");
        $form->addButton("Efficacité V\n§e1000 $");
        $form->sendToPlayer($sender);
    }
}