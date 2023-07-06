<?php

namespace UnknowL\items;

use pocketmine\event\Listener;
use pocketmine\event\player\PlayerItemUseEvent;
use pocketmine\item\StringToItemParser;
use pocketmine\item\VanillaItems;

class Soup implements Listener
{

    public function onInteract(PlayerItemUseEvent $event)
    {
        $item = $event->getItem();
        $player = $event->getPlayer();
        $id = $item->getTypeId();
        $heal = $player->getHealth();
        if ($id == VanillaItems::SLIMEBALL()->getTypeId()) {
            if ($heal >= 20) {
                return true;
            } elseif ($heal <= 19 && $heal > 18) {
                $player->setHealth(20);
                $player->getInventory()->removeItem(VanillaItems::SLIMEBALL()->setCount(1));
                //$player->sendPopup("§f+§d1");
            } elseif ($heal <= 18 && $heal > 17) {
                $player->setHealth(20);
                $player->getInventory()->removeItem(VanillaItems::SLIMEBALL()->setCount(1));
              //$player->sendPopup("§f+§d2");
            } elseif ($heal <= 17 && $heal > 16) {
                $player->setHealth(20);
                $player->getInventory()->removeItem(VanillaItems::SLIMEBALL()->setCount(2));
              //$player->sendPopup("§f+§d3");
            } elseif ($heal <= 16 && $heal > 15) {
                $player->setHealth(20);
                $player->getInventory()->removeItem(VanillaItems::SLIMEBALL()->setCount(2));
              //$player->sendPopup("§f+§d4");
            } elseif ($heal <= 15&& $heal > 14) {
                $player->setHealth(20);
                $player->getInventory()->removeItem(VanillaItems::SLIMEBALL()->setCount(3));
              //$player->sendPopup("§f+§d5");
            } elseif ($heal <= 14&& $heal > 13) {
                $player->setHealth(20);
                $player->getInventory()->removeItem(VanillaItems::SLIMEBALL()->setCount(3));
                //$player->sendPopup("§f+§d6");
            } elseif ($heal <= 13&& $heal > 12) {
                $player->setHealth(20);
                $player->getInventory()->removeItem(VanillaItems::SLIMEBALL()->setCount(4));
                //$player->sendPopup("§f+§d7");
            } elseif ($heal <= 12&& $heal > 11) {
                $player->setHealth(20);
                $player->getInventory()->removeItem(VanillaItems::SLIMEBALL()->setCount(4));
              //$player->sendPopup("§f+§d8");
            }elseif ($heal <= 11&& $heal > 10) {
                $player->setHealth(20);
                $player->getInventory()->removeItem(VanillaItems::SLIMEBALL()->setCount(5));
               //$player->sendPopup("§f+§d9");
            } elseif ($heal <= 10&& $heal > 9) {
                $player->setHealth(20);
                $player->getInventory()->removeItem(VanillaItems::SLIMEBALL()->setCount(5));
               //$player->sendPopup("§f+§d10");
            } elseif ($heal <= 9&& $heal > 8) {
                $player->setHealth(20);
                $player->getInventory()->removeItem(VanillaItems::SLIMEBALL()->setCount(6));
               //$player->sendPopup("§f+§d11");
            } elseif ($heal <= 8&& $heal > 7) {
                $player->setHealth(20);
                $player->getInventory()->removeItem(VanillaItems::SLIMEBALL()->setCount(6));
               // $player->sendPopup("§f+§d12");
            } elseif ($heal <= 7&& $heal > 6) {
                $player->setHealth(20);
                $player->getInventory()->removeItem(VanillaItems::SLIMEBALL()->setCount(7));
               // $player->sendPopup("§f+§d13");
            } elseif ($heal <= 6&& $heal > 5) {
                $player->setHealth(20);
                $player->getInventory()->removeItem(VanillaItems::SLIMEBALL()->setCount(7));
               // $player->sendPopup("§f+§d14");
            } elseif ($heal <= 5&& $heal > 4) {
                $player->setHealth(20);
                $player->getInventory()->removeItem(VanillaItems::SLIMEBALL()->setCount(8));
                //$player->sendPopup("§f+§d15");
            } elseif ($heal <= 4&& $heal > 3) {
                $player->setHealth(20);
                $player->getInventory()->removeItem(VanillaItems::SLIMEBALL()->setCount(8));
               // $player->sendPopup("§f+§d16");
            } elseif ($heal <= 3&& $heal > 2) {
                $player->setHealth(20);
                $player->getInventory()->removeItem(VanillaItems::SLIMEBALL()->setCount(9));
               // $player->sendPopup("§f+§d17");
            } elseif ($heal <= 2&& $heal > 1) {
                $player->setHealth(20);
                $player->getInventory()->removeItem(VanillaItems::SLIMEBALL()->setCount(9));
                //$player->sendPopup("§f+§d18");
            } elseif ($heal <= 1&& $heal > 0) {
                $player->setHealth(20);
                $player->getInventory()->removeItem(VanillaItems::SLIMEBALL()->setCount(10));
                //$player->sendPopup("§f+§d19");
            }
        }
    }
}