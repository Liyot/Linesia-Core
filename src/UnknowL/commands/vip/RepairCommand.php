<?php

namespace UnknowL\commands\vip;

use pocketmine\command\CommandSender;
use pocketmine\item\Durable;
use pocketmine\player\Player;
use UnknowL\handlers\specific\RepairHandler;
use UnknowL\lib\commando\BaseCommand;
use UnknowL\lib\commando\constraint\InGameRequiredConstraint;
use UnknowL\lib\forms\bootstrap\Main;
use UnknowL\lib\simplepackethandler\utils\Utils;
use UnknowL\Linesia;
use UnknowL\player\LinesiaPlayer;

class RepairCommand extends BaseCommand
{

    public function __construct()
    {
        $settings = Linesia::getInstance()->getCommandManager()->getSettings("repair");
        parent::__construct(Linesia::getInstance(), $settings->getName(), $settings->getDescription(), $settings->getAliases());
    }

    protected function prepare(): void
    {
        $this->setPermission("repair.use");
        $this->addConstraint(new InGameRequiredConstraint($this));
    }

    /**
     * @param LinesiaPlayer $sender
     */
    public function onRun(CommandSender $sender, string $aliasUsed, array $args): void
    {
        if ($sender instanceof Player) {
            if (!isset($args[0])) {
                if (RepairHandler::getCooldown()->exists($sender->getXuid() . "-repair")) {
                    if (time() > RepairHandler::getCooldown()->get($sender->getXuid() . "-repair")) {
                        $item = $sender->getInventory()->getItemInHand();
                        if ($item instanceof Durable) {
                            if ($item->getDamage() >= 5) {
                                $item->setDamage(0);
                                $sender->getInventory()->setItemInHand($item);
                                $config = RepairHandler::getCooldown();
                                $config->set($sender->getXuid() . "-repair", time() + 600);
                                $config->save();
                                return;
                            } else {
                                $sender->sendMessage("§cL'item dans votre main a déjà sa durabilité au maximum.");
                                return;
                            }
                        } else {
                            $sender->sendMessage("§cL'item dans votre main ne possède pas de durabilité.");
                            return;
                        }
                    } else {
                        $time = RepairHandler::convert(RepairHandler::getCooldown()->get($sender->getXuid() . "-repair") - time());
                        $sender->sendMessage("§cVous pourrez réutiliser cette commande dans $time");
                        return;
                    }
                } else {
                    $item = $sender->getInventory()->getItemInHand();
                    if ($item instanceof Durable) {
                        if ($item->getDamage() >= 5) {
                            $item->setDamage(0);
                            $sender->getInventory()->setItemInHand($item);
                            $config = RepairHandler::getCooldown();
                            $config->set($sender->getXuid() . "-repair", time() + 600);
                            $config->save();
                            return;
                        } else {
                            $sender->sendMessage("§cL'item dans votre main a déjà sa durabilité au maximum.");
                            return;
                        }
                    } else {
                        $sender->sendMessage("§cL'item dans votre main ne possède pas de durabilité.");
                        return;
                    }
                }
            } else {
                if ($sender->hasPermission("repair.all.use")) {
                    if (strtolower($args[0]) === "all") {
                        if (RepairHandler::getCooldown()->exists($sender->getXuid() . "-repair-all")) {
                            if (time() >= RepairHandler::getCooldown()->get($sender->getXuid() . "-repair-all")) {
                                $count = 0;
                                foreach ($sender->getInventory()->getContents() as $slot => $item) {
                                    if ($item instanceof Durable) {
                                        $item->setDamage(0);
                                        $sender->getInventory()->setItem($slot, $item);
                                        $count++;
                                    }
                                }
                                foreach ($sender->getArmorInventory()->getContents() as $slot => $item) {
                                    if ($item instanceof Durable) {
                                        $item->setDamage(0);
                                        $sender->getArmorInventory()->setItem($slot, $item);
                                        $count++;
                                    }
                                }
                                $config = RepairHandler::getCooldown();
                                $config->set($sender->getXuid() . "-repair-all", time() + 600);
                                $config->save();
                                $sender->sendMessage("§aTous les items viennent d'être réparé.");
                                return;
                            } else {
                                $time = RepairHandler::convert(RepairHandler::getCooldown()->get($sender->getXuid() . "-repair-all") - time());
                                $sender->sendMessage("§cVous pourrez réutiliser cette commande dans $time");
                                return;
                            }
                        } else {
                            $count = 0;
                            foreach ($sender->getInventory()->getContents() as $slot => $item) {
                                if ($item instanceof Durable) {
                                    $item->setDamage(0);
                                    $sender->getInventory()->setItem($slot, $item);
                                    $count++;
                                }
                            }
                            foreach ($sender->getArmorInventory()->getContents() as $slot => $item) {
                                if ($item instanceof Durable) {
                                    $item->setDamage(0);
                                    $sender->getArmorInventory()->setItem($slot, $item);
                                    $count++;
                                }
                            }
                            $config = RepairHandler::getCooldown();
                            $config->set($sender->getXuid() . "-repair-all", time() + 600);
                            $config->save();
                            $sender->sendMessage("§aTous les items viennent d'être réparé.");
                        }
                    } else {
                        $sender->sendMessage("§cVeuillez faire /repair all.");
                    }
                } else {
                    $sender->sendMessage("§cVous n'avez pas la permission d'utiliser cette commande.");
                }
            }
        }
    }
}