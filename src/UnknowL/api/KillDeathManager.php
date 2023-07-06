<?php

namespace UnknowL\api;

use pocketmine\event\entity\EntityDamageEvent;
use pocketmine\player\Player;
use pocketmine\Server;

class KillDeathManager {

    /**
     * @param Player $player
     * @param int $cause
     * @param mixed $damager
     * @param array $configMessage
     * @return void
     */
    public static function sendDeathMessage(Player $player, int $cause, mixed $damager): void {

        $serverInstance = Server::getInstance();
        $playerName = $player->getName();

        if($damager instanceof Player) {
            $damagerName = $damager->getName();
            if($damager->getInventory() !== null && $damager->getInventory()->getItemInHand() !== null && $damager->getInventory()->getItemInHand()->getCustomName() !== ""){
                $item = $damager->getInventory()->getItemInHand()->getCustomName();
            } else {
                $item = $damager->getInventory()->getItemInHand()->getName()."§r";
            }
        } else {
            $damagerName = "Mob";
            $itemName = "";
        }

        $replace = $item ?? "";

        switch ($cause) {
            case EntityDamageEvent::CAUSE_ENTITY_ATTACK:
                $serverInstance->broadcastMessage("§d§l» §r§d$playerName §fa été tué par §d$damagerName §favec §d$replace §f!");
                break;
            case EntityDamageEvent::CAUSE_FALL:
                $serverInstance->broadcastMessage("§d§l» §r§d$playerName §fest mort de dégat de chûte !");
                break;
            case EntityDamageEvent::CAUSE_LAVA:
                $serverInstance->broadcastMessage("§d§l» §r§d$playerName §fest mort par la lave !");
                break;
            case EntityDamageEvent::CAUSE_DROWNING:
                $serverInstance->broadcastMessage("§d§l» §r§d$playerName §fest mort de noyade !");
                break;
            case EntityDamageEvent::CAUSE_ENTITY_EXPLOSION:
                $serverInstance->broadcastMessage("§d§l» §r§d$playerName §fest mort d'explosion !");
                break;
            case EntityDamageEvent::CAUSE_SUFFOCATION:
                $serverInstance->broadcastMessage("§d§l» §r§d$playerName §fest mort suffocation !");
                break;
            case EntityDamageEvent::CAUSE_FIRE:
                $serverInstance->broadcastMessage("§d§l» §r§d$playerName §fest mort de feu !");
                break;
            case EntityDamageEvent::CAUSE_FIRE_TICK:
                $serverInstance->broadcastMessage("§d§l» §r§d$playerName §fest mort de feu !");
                break;
            case EntityDamageEvent::CAUSE_PROJECTILE:
                $serverInstance->broadcastMessage("§d§l» §r§d$playerName §fest mort d'un projectile !");
                break;
            case
                $serverInstance->broadcastMessage("§d§l» §r§d$playerName §fest mort de manière innatendue !");
                break;
        }
    }
}