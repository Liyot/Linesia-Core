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

class MsgCommand extends BaseCommand
{

    public static array $msg = [];

    public function __construct()
    {
        $settings = Linesia::getInstance()->getCommandManager()->getSettings("msg");
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
            if (isset($args[0])) {
                $player = Server::getInstance()->getPlayerByPrefix($args[0]);
                if ($player instanceof Player) {
                    if (SettingsAPI::isEnableSettings($player, "msg")) {
                        if (isset($args[1])) {
                            $msg = "";
                            for ($i = 1; $i < count($args); $i++) {
                                $msg .= $args[$i];
                                $msg .= " ";
                            }
                            self::$msg[$sender->getName()] = $player->getName();
                            self::$msg[$player->getName()] = $sender->getName();
                            $sender->sendMessage("§cMOI§7 -> §c{$player->getName()}§7: $msg");
                            $player->sendMessage("§c{$sender->getName()}§7 -> §cMOI§7: $msg");
                        } else $sender->sendMessage("§cVous devez indiquer un message !");
                    } else $sender->sendMessage("§cLe joueur indiqué n'accepte pas les message privé !");
                } else $sender->sendMessage("§cLe joueur indiqué n'est pas connecté !");
            } else $sender->sendMessage("§cVous devez indiquer un joueur !");
        } else $sender->sendMessage("§cLa commande doit être executer en jeu !");
    }
}