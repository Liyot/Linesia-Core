<?php

namespace UnknowL\handlers\specific;

use pocketmine\utils\Config;
use UnknowL\Linesia;

final class RepairHandler
{

    private static Config $cooldown;

    public static function setup(): void
    {
        self::$cooldown = new Config(Linesia::getInstance()->getDataFolder() . "repair.json", Config::JSON);
    }

    public static function getCooldown(): Config{
        return self::$cooldown;
    }

    public static function convert(int $int, string $color1 = "§c", string $color2 = "§c")
    {
        $day = floor($int / 86400);
        $hourSec = $int % 86400;
        $hour = floor($hourSec / 3600);
        $minuteSec = $hourSec % 3600;
        $minute = floor($minuteSec / 60);
        $remainingSec = $minuteSec % 60;
        $second = ceil($remainingSec);
        if(!isset($day)) $day = 0;
        if (!isset($hour)) $hour = 0;
        if (!isset($minute)) $minute = 0;
        if (!isset($second)) $second = 0;

        return "$color1" . $day . " jour(s)$color2, $color1" . $hour . " heure(s)$color2, $color1" . $minute . " minute(s)$color2 et $color1" . $second . " seconde(s){$color2}";
    }
}
