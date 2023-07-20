<?php

namespace UnknowL\api;

use JetBrains\PhpStorm\Pure;
use pocketmine\network\mcpe\protocol\RemoveObjectivePacket;
use pocketmine\network\mcpe\protocol\SetDisplayObjectivePacket;
use pocketmine\network\mcpe\protocol\SetScorePacket;
use pocketmine\network\mcpe\protocol\types\ScorePacketEntry;
use pocketmine\player\Player;
use pocketmine\Server;
use UnknowL\commands\vote\VoteCommand;
use UnknowL\handlers\Handler;
use UnknowL\player\LinesiaPlayer;

class ScoreBoardAPI {

    private static array $scoreboard = [];

    public static function sendScoreboard(LinesiaPlayer $player): void
    {
        if (self::isScoreboardEnable($player)) self::removeScoreboard($player);

        $online = count($player->getServer()->getOnlinePlayers());

        /*$rank = RolePlayerManager::getInstance()->getPlayer($player)?->getRoleName();

        $solde = Server::getInstance()->getPluginManager()->getPlugin("EconomyAPI");
        assert($solde instanceof EconomyAPI);
        $money = $solde->myMoney($player);*/

        $money = $player->getEconomyManager()->getMoney();
        $rank = $player->getRank()->getName();
		$votePart = VoteCommand::getInstance()->getVoteParty();

        self::$scoreboard[] = $player->getName();
        self::lineTitle($player, "§5- §dLinesia V8 §5-");
        self::lineCreate($player, 1, "  ");
        self::lineCreate($player, 2, " §l§5» §d{$player->getName()}");
        self::lineCreate($player, 3, " Grade: §d$rank");
        self::lineCreate($player, 4, " Money: §d$money");
		self::lineCreate($player, 5, " VoteParty: §d$votePart");
        self::lineCreate($player, 6, " Connecté(s): §d$online");
        self::lineCreate($player, 7, "");
        self::lineCreate($player, 8, "§5linesia.eu");
    }

    //use linesia\core\api\ScoreBoardAPI;
    //ScoreBoardAPI::updateMoney($player);
    public static function updateMoney(LinesiaPlayer $player): void
    {
        if (self::isScoreboardEnable($player)) {

            $money = $player->getEconomyManager()->getMoney();

            self::lineRemove($player, 4);
            self::lineCreate($player, 4, " Money: §d$money");
        }
    }

    /*use linesia\core\api\ScoreBoardAPI;
                    $co = Server::getInstance()->getPlayerExact($player);
                if ($co === null) {
                    // Le joueur n'est pas en ligne
                } else {
                    ScoreBoardAPI::updateRank($player);
                }
    ScoreBoardAPI::updateRank($player);*/
    public static function updateRank(LinesiaPlayer $player): void
    {
        $rank = $player->getRank()->getName();

        if (self::isScoreboardEnable($player)) {
            self::lineRemove($player, 3);
            self::lineCreate($player, 3, " Grade: §d$rank");
        }
    }

    public static function updateServer($removeOne = false): void
    {
        $online = $removeOne ? count(Server::getInstance()->getOnlinePlayers()) - 1 : count(Server::getInstance()->getOnlinePlayers());
        /** @var LinesiaPlayer $player */
        foreach (Server::getInstance()->getOnlinePlayers() as $player) {
            self::lineRemove($player, 6);
            self::lineCreate($player, 6, " Connectés: §d$online");
        }
    }

    #[Pure] public static function isScoreboardEnable(Player $player): bool
    {
        return in_array($player->getName(), self::$scoreboard);
    }

	public static function updateVoteParty(LinesiaPlayer $player, int $actual): void
	{
		self::lineRemove($player, 5);
		self::lineCreate($player, 5, " VoteParty: §d$actual");
	}

    public static function removeScoreboard(Player $player): void
    {
        if (self::isScoreboardEnable($player) === false) return;

        $packet = new RemoveObjectivePacket();
        $packet->objectiveName = "objective";
        $player->getNetworkSession()->sendDataPacket($packet);
        unset(self::$scoreboard[$player->getName()]);
    }

    public static function lineTitle(Player $player, string $title): void
    {
        if (self::isScoreboardEnable($player) === false) return;

        $packet = new SetDisplayObjectivePacket();
        $packet->displaySlot = "sidebar";
        $packet->objectiveName = "objective";
        $packet->displayName = $title;
        $packet->criteriaName = "dummy";
        $packet->sortOrder = 0;
        $player->getNetworkSession()->sendDataPacket($packet);
    }

    public static function lineCreate(Player $player, int $line, string $content): void
    {
        if (self::isScoreboardEnable($player) === false) return;

        $packetline = new ScorePacketEntry();
        $packetline->objectiveName = "objective";
        $packetline->type = ScorePacketEntry::TYPE_FAKE_PLAYER;
        $packetline->customName = "" . $content . "   ";
        $packetline->score = $line;
        $packetline->scoreboardId = $line;
        $packet = new SetScorePacket();
        $packet->type = SetScorePacket::TYPE_CHANGE;
        $packet->entries[] = $packetline;
        $player->getNetworkSession()->sendDataPacket($packet);
    }

    public static function lineRemove(Player $player, int $line): void
    {
        if (self::isScoreboardEnable($player) === false) return;

        $entry = new ScorePacketEntry();
        $entry->objectiveName = "objective";
        $entry->score = $line;
        $entry->scoreboardId = $line;
        $packet = new SetScorePacket();
        $packet->type = SetScorePacket::TYPE_REMOVE;
        $packet->entries[] = $entry;
        $player->getNetworkSession()->sendDataPacket($packet);
    }
}