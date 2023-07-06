<?php

namespace UnknowL\commands\admin;

use pocketmine\command\CommandSender;
use pocketmine\command\utils\InvalidCommandSyntaxException;
use pocketmine\item\LegacyStringToItemParser;
use pocketmine\item\LegacyStringToItemParserException;
use pocketmine\item\StringToItemParser;
use pocketmine\player\Player;
use pocketmine\Server;
use UnknowL\lib\commando\BaseCommand;
use UnknowL\lib\commando\constraint\InGameRequiredConstraint;
use UnknowL\Linesia;
use UnknowL\player\LinesiaPlayer;

class GiveAllCommand extends BaseCommand
{

    public function __construct()
    {
        $settings = Linesia::getInstance()->getCommandManager()->getSettings("giveall");
        parent::__construct(Linesia::getInstance(), $settings->getName(), $settings->getDescription(), $settings->getAliases());
    }

    protected function prepare(): void
    {
        $this->setPermission("pocketmine.group.op");
        $this->addConstraint(new InGameRequiredConstraint($this));
    }

    /**
     * @param LinesiaPlayer $sender
     */
    public function onRun(CommandSender $sender, string $aliasUsed, array $args): void
    {
        if ($sender instanceof Player) {
            if (count($args) < 2) {
                throw new InvalidCommandSyntaxException();
            }
            try {
                $item = StringToItemParser::getInstance()->parse($args[0]) ?? LegacyStringToItemParser::getInstance()->parse($args[0]);
            } catch (LegacyStringToItemParserException $e) {
                $sender->sendMessage("§cItem non trouvé !");
                return;
            }
            if (isset($args[1])) {
                if (is_numeric($args[1]) === true) {
                    $i = $args[1];
                    $count = (int)$i;

                } else {
                    $count = 1;
                }
            }
            $item->setCount($count);

            foreach (Server::getInstance()->getOnlinePlayers() as $player) {
                $player->getInventory()->addItem($item);
                $player->sendMessage("§aTous les joueurs ont reçu un item dans un giveall !");
            }
        }
    }

}