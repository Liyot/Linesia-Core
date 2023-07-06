<?php

namespace UnknowL\commands\default;

use pocketmine\command\CommandSender;
use pocketmine\entity\effect\EffectInstance;
use pocketmine\entity\effect\VanillaEffects;
use pocketmine\player\Player;
use UnknowL\lib\commando\BaseCommand;
use UnknowL\lib\commando\constraint\InGameRequiredConstraint;
use UnknowL\Linesia;
use UnknowL\player\LinesiaPlayer;

class NvCommand extends BaseCommand
{

    public function __construct()
    {
        $settings = Linesia::getInstance()->getCommandManager()->getSettings("nv");
        parent::__construct(Linesia::getInstance(), $settings->getName(), $settings->getDescription(), $settings->getAliases());
    }

    protected function prepare(): void
    {
        $this->setPermission("pocketmine.group.user");
        $this->addConstraint(new InGameRequiredConstraint($this));
    }

    /**
     * @param LinesiaPlayer $sender
     */
    public function onRun(CommandSender $sender, string $aliasUsed, array $args): void
    {
        if($sender instanceof Player) {

            if ($sender->getEffects()->has(VanillaEffects::NIGHT_VISION())) {
                $sender->getEffects()->remove(VanillaEffects::NIGHT_VISION());
                $sender->sendMessage("§cVous avez désactivé le Nightvision.");
            } else {
                $sender->getEffects()->add(new EffectInstance(VanillaEffects::NIGHT_VISION(), 9999999,0,false));
                $sender->sendMessage("§aVous avez activé le Nightvision.");
            }

        }
    }
}