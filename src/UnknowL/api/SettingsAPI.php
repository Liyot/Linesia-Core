<?php

namespace UnknowL\api;

use pocketmine\utils\Config;
use pocketmine\Server;
use UnknowL\commands\settings\SettingsCommand;
use UnknowL\handlers\Handler;
use UnknowL\Linesia;

class SettingsAPI
{
    public static Config $data;

    public function __construct()
    {
        self::$data = new Config(Linesia::getInstance()->getDataFolder() . "Settings.json", Config::JSON);

        Server::getInstance()->getCommandMap()->register("", new SettingsCommand());
    }

    public static function createPlayer($player): void
    {
        if (!self::existPlayer($player)) {
            self::$data->set(Handler::getPlayerName($player), ["scoreboard" => true, "msg" => true, "cps" => false, "coordonnees" => true]);
            self::$data->save();
        }
        $coord = new CoordonneesAPI();
        $coord->coordonnees($player);
    }

    public static function existPlayer($player): bool
    {
        return self::$data->exists(Handler::getPlayerName($player));
    }

    public static function isEnableSettings($player, string $settings): bool
    {
        return self::$data->get(Handler::getPlayerName($player))[$settings];
    }

    public static function setSettings($player, string $settings, bool $bool): void
    {
        self::$data->setNested(Handler::getPlayerName($player) . ".$settings", $bool);
        self::$data->save();
        $coord = new CoordonneesAPI();
        $coord->coordonnees($player);
    }
}