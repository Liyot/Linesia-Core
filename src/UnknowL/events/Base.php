<?php

namespace Boss;

use pocketmine\console\ConsoleCommandSender;
use pocketmine\entity\EntityDataHelper;
use pocketmine\entity\EntityFactory;
use pocketmine\nbt\tag\CompoundTag;
use pocketmine\permission\DefaultPermissions;
use pocketmine\permission\Permission;
use pocketmine\permission\PermissionManager;
use pocketmine\plugin\PluginBase;
use pocketmine\scheduler\Task;
use pocketmine\utils\SingletonTrait;
use pocketmine\world\World;

class Base extends PluginBase
{
    use SingletonTrait;

    protected function onLoad(): void
    {
        self::setInstance($this);
        $this->saveDefaultConfig();
    }

    protected function onEnable(): void
    {
        $config = $this->getConfig();

        date_default_timezone_set($config->get("timezone"));

        $this->getScheduler()->scheduleRepeatingTask(new class extends Task {
            public function onRun(): void
            {
                $config = Base::getInstance()->getConfig();
                $date = date("H:i");

                foreach ($config->get("auto-start") as $time => $boss) {
                    if ($time === $date) {
                        $server = Base::getInstance()->getServer();
                        $server->dispatchCommand(new ConsoleCommandSender($server, $server->getLanguage()), "spawnboss " . $boss . " random");
                    }
                }
            }
        }, 31 * 20);

        $permManager = PermissionManager::getInstance();
        $opRoot = $permManager->getPermission(DefaultPermissions::ROOT_OPERATOR);

        $permManager->addPermission(new Permission($config->get("command-permission")));
        $opRoot->addChild($config->get("command-permission"), true);

        $this->saveResource("steve.png");

        EntityFactory::getInstance()->register(BossEntity::class, function (World $world, CompoundTag $nbt): BossEntity {
            return new BossEntity(EntityDataHelper::parseLocation($nbt, $world), BossEntity::parseSkinNBT($nbt), $nbt);
        }, ["BossEntity"]);
    }
}