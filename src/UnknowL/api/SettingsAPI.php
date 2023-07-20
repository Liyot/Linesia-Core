<?php

namespace UnknowL\api;

use pocketmine\command\CommandSender;
use pocketmine\utils\Config;
use pocketmine\Server;
use UnknowL\commands\settings\SettingsCommand;
use UnknowL\handlers\Handler;
use UnknowL\Linesia;
use UnknowL\player\LinesiaPlayer;

class SettingsAPI
{
    public static Config $data;

    public function __construct()
    {
        self::$data = new Config(Linesia::getInstance()->getDataFolder() . "Settings.json", Config::JSON);
    }

    public static function createPlayer($player): void
    {
        if (!self::existPlayer($player)) {
            self::$data->set(self::getPlayerName($player), ["scoreboard" => true, "msg" => true, "cps" => false, "coordonnees" => true]);
            self::$data->save();
        }
        $coord = new CoordonneesAPI();
        $coord->coordonnees($player);
    }

    public static function existPlayer($player): bool
    {
        return self::$data->exists(self::getPlayerName($player));
    }

    public static function isEnableSettings($player, string $settings): bool
    {
        return self::$data->get(self::getPlayerName($player))[$settings];
    }

    public static function setSettings($player, string $settings, bool $bool): void
    {
        self::$data->setNested(self::getPlayerName($player) . ".$settings", $bool);
        self::$data->save();
        $coord = new CoordonneesAPI();
        $coord->coordonnees($player);
    }

	public static function getPlayerName($player): string
	{
		if ($player instanceof LinesiaPlayer) return $player->getDisplayName(); elseif ($player instanceof CommandSender) return "Serveur";
		else return $player;
	}
}