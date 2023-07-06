<?php

namespace UnknowL\api;

use pocketmine\player\Player;
use pocketmine\scheduler\ClosureTask;
use pocketmine\scheduler\TaskHandler;
use UnknowL\Linesia;

class CombatLoggerManager {

    /** @var array */
    public static array $isLogged = [];

    public static function updateLog(Player $player): void {

        if ($player->isConnected()) {
            if (isset(CombatLoggerManager::$isLogged[$player->getName()])) {

                /** @var TaskHandler $handler */
                $handler = CombatLoggerManager::$isLogged[$player->getName()]["task"];
                $handler->getTask()->getHandler()->cancel();
            }

            $task = Linesia::getInstance()->getScheduler()->scheduleDelayedTask(new ClosureTask(function () use ($player) {
                unset(self::$isLogged[$player->getName()]);
                $player->sendMessage("§aVous n'êtes plus en combat !");
            }), (20*15));

            CombatLoggerManager::$isLogged[$player->getName()] = ["time" => time() + 15, "task" => $task];
        }
    }
}