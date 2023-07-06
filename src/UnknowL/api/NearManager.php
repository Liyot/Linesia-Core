<?php

namespace UnknowL\api;

use pocketmine\player\Player;

class NearManager {

    /**
     * @param Player $player
     * @return void
     */
    public static function sendNear(Player $player): void {

        $baseArray = [];

        $playerPos = $player->getPosition();
        $nearStatus = false;

        foreach ($player->getWorld()->getPlayers() as $players) {

            $targetPos = $players->getPosition();
            $distanceBlocs = $playerPos->distance($targetPos);

            if($distanceBlocs <= 25) {
                if($player !== $players) {

                    $nearPlayers = "\n" . $players->getName() . " - " . floor($distanceBlocs) . " Bloc(s)";
                    $baseArray[] = $nearPlayers;
                    $nearStatus = true;
                }
            }
        }

        $near = implode($baseArray);
        if($nearStatus)
            $player->sendMessage("§dVoici les personnes autour de vous : §5$near\n");
        else
            $player->sendMessage("§cPersonne ne se trouve autour de vous !");
    }
}







