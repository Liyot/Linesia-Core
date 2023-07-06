<?php

namespace UnknowL\form;

use jojoe77777\FormAPI\CustomForm;
use pocketmine\player\Player;
use UnknowL\api\ScoreBoardAPI;
use UnknowL\api\SettingsAPI;

class SettingsForms
{
    public static function mainForm(Player $player): CustomForm
    {
        $form = new CustomForm(function (Player $player, array $data = null) {
            if ($data === null) return;

            if (SettingsAPI::isEnableSettings($player, "msg") !== $data[1]) {
                SettingsAPI::setSettings($player, "msg", !SettingsAPI::isEnableSettings($player, "msg"));
                SettingsAPI::isEnableSettings($player, "msg") ? $player->sendMessage("§aVous avez débloqué vos messages privés !") : $player->sendMessage("§cVous bloqué vos messages privés !");
            }

            if (SettingsAPI::isEnableSettings($player, "scoreboard") !== $data[2]) {
                SettingsAPI::setSettings($player, "scoreboard", !SettingsAPI::isEnableSettings($player, "scoreboard"));
                SettingsAPI::isEnableSettings($player, "scoreboard") ? $player->sendMessage("§aVous avez activé votre scoreboard !") : $player->sendMessage("§vVous avez désactivé votre scoreboard !");
                SettingsAPI::isEnableSettings($player, "scoreboard") ? ScoreBoardAPI::sendScoreboard($player) : ScoreBoardAPI::removeScoreboard($player);
            }

            if (SettingsAPI::isEnableSettings($player, "cps") !== $data[3]) {
                SettingsAPI::setSettings($player, "cps", !SettingsAPI::isEnableSettings($player, "cps"));
                SettingsAPI::isEnableSettings($player, "cps") ? $player->sendMessage("§aVous avez activé vos cps !") : $player->sendMessage("§cVous avez désactivé vos cps !");
            }

            if (SettingsAPI::isEnableSettings($player, "coordonnees") !== $data[3]) {
                SettingsAPI::setSettings($player, "coordonnees", !SettingsAPI::isEnableSettings($player, "coordonnees"));
                SettingsAPI::isEnableSettings($player, "coordonnees") ? $player->sendMessage("§aVous avez activé vos coordonnées !") : $player->sendMessage("§cVous avez désactivé vos coordonnées !");
            }
        });
        $form->setTitle("§d- §fSettings §d-");
        $form->addLabel("Choisissez les options que vous voulez pour pouvoir customiser votre experience de jeu:");
        $form->addToggle("Messages privés", SettingsAPI::isEnableSettings($player, "msg"));
        $form->addToggle("Scoreboard", SettingsAPI::isEnableSettings($player, "scoreboard"));
        $form->addToggle("Cps", SettingsAPI::isEnableSettings($player, "cps"));
        $form->addToggle("Coordonnées", SettingsAPI::isEnableSettings($player, "coordonnees"));
        $form->sendToPlayer($player);
        return $form;
    }
}