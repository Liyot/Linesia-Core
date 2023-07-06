<?php

namespace UnknowL\commands\default;

use pocketmine\command\CommandSender;
use pocketmine\player\Player;
use pocketmine\Server;
use UnknowL\api\SettingsAPI;
use UnknowL\lib\commando\BaseCommand;
use UnknowL\lib\commando\constraint\InGameRequiredConstraint;
use UnknowL\Linesia;
use UnknowL\player\LinesiaPlayer;

class ReplyCommand extends BaseCommand
{

    public function __construct()
    {
        $settings = Linesia::getInstance()->getCommandManager()->getSettings("reply");
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
        if ($sender instanceof Player) {
            if (!empty(MsgCommand::$msg[$sender->getName()])) {
                $player = Server::getInstance()->getPlayerByPrefix(MsgCommand::$msg[$sender->getName()]);
                if ($player instanceof Player) {
                    if (SettingsAPI::isEnableSettings($player, "msg")) {
                        if (isset($args[0])) {
                            $msg = "";
                            for ($i = 0; $i < count($args); $i++) {
                                $msg .= $args[$i];
                                $msg .= " ";
                            }
                            $sender->sendMessage("§cMOI§7 -> §c{$player->getName()}§7: $msg");
                            $player->sendMessage("§c{$sender->getName()}§7 -> §cMOI§7: $msg");
                        } else $sender->sendMessage("§cVous devez indiquer un message !");
                    } else $sender->sendMessage("§cLe joueur à qui vous deviez répondre à désactiver ses messages privés !");
                } else $sender->sendMessage("§cLe joueur à qui vous deviez répondre est déconnecté !");
            } else $sender->sendMessage("§cVous n'avez personne à qui répondre !");
        }
    }
}