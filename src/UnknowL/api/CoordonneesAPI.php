<?php

namespace UnknowL\api;

use pocketmine\network\mcpe\protocol\GameRulesChangedPacket;
use pocketmine\network\mcpe\protocol\types\BoolGameRule;
use pocketmine\player\Player;

class CoordonneesAPI
{
    public function coordonnees(Player $player){

        if (SettingsAPI::isEnableSettings($player, "coordonnees")) {
            $pk = new GameRulesChangedPacket();
            $pk->gameRules = ["showcoordinates" => new BoolGameRule(true, false)];
            $player->getNetworkSession()->sendDataPacket($pk);
        } else {
            $pk = new GameRulesChangedPacket();
            $pk->gameRules = ["showcoordinates" => new BoolGameRule(false, false)];
            $player->getNetworkSession()->sendDataPacket($pk);
        }
    }
}