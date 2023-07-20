<?php

namespace UnknowL\commands\admin;

use pocketmine\command\CommandSender;
use pocketmine\entity\Entity;
use pocketmine\entity\Human;
use pocketmine\entity\object\ExperienceOrb;
use pocketmine\entity\object\ItemEntity;
use pocketmine\entity\Villager;
use pocketmine\permission\DefaultPermissions;
use pocketmine\Server;
use UnknowL\lib\commando\args\StringArgument;
use UnknowL\lib\commando\BaseCommand;
use UnknowL\lib\commando\constraint\InGameRequiredConstraint;
use UnknowL\Linesia;
use UnknowL\player\LinesiaPlayer;
use UnknowL\task\ClearlagTask;

class ClearLaggCommand extends BaseCommand
{

    public function __construct()
    {
        $settings = Linesia::getInstance()->getCommandManager()->getSettings("clearlag");
        parent::__construct(Linesia::getInstance(), $settings->getName(), $settings->getDescription(), $settings->getAliases());
    }

    protected function prepare(): void
    {
        $this->setPermission("pocketmine.group.user");
		$this->registerArgument(0, new StringArgument("force", true));
        $this->addConstraint(new InGameRequiredConstraint($this));
    }

    /**
     * @param LinesiaPlayer $sender
     */
    public function onRun(CommandSender $sender, string $aliasUsed, array $args): void
    {
        if (isset($args[0]) and ($args[0] === "force") and Server::getInstance()->isOp($sender->getName())) {
            $count = 0;
            foreach (Server::getInstance()->getWorldManager()->getWorlds() as $world) {
                foreach ($world->getEntities() as $entity) {
                    if (($entity instanceof ExperienceOrb) or ($entity instanceof ItemEntity) or ($entity instanceof Entity && !$entity instanceof Human && !$entity instanceof Villager)) {
                        $entity->flagForDespawn();
                        $count++;
                    }
                }
            }
            Server::getInstance()->broadcastMessage("§d§l» §r§fUn staff a forcé le clearlagg ! Il y a eu un total de§d $count §fentité(s) supprimé !");
            $sender->sendMessage("§aVous avez bien forcé le clearlagg !");
        } else $sender->sendMessage("§d§l» §r§fLe prochain clearagg sera dans§d " . ClearLagTask::$time / 20 . " §fseconde(s) !");
    }
}