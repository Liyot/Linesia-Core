<?php

namespace UnknowL\task;

use pocketmine\item\StringToItemParser;
use pocketmine\item\VanillaItems;
use pocketmine\player\Player;
use pocketmine\scheduler\Task;
use pocketmine\Server;

class PurifTask extends Task {

	public function onRun(): void
	{
		$world = Server::getInstance()->getWorldManager()->getWorldByName("world");
		foreach ($world->getPlayers() as $player){
			if ($this->isPurifZone($player, ["257", "144", "397"], ["253", "150", "401"])) {
				$item = $player->getInventory()->getItemInHand();
				if($item->getTypeId() === VanillaItems::DIAMOND()->getTypeId()){
					$player->getInventory()->removeItem(StringToItemParser::getInstance()->parse("diamond"));
					$player->getInventory()->addItem(StringToItemParser::getInstance()->parse("emerald"));

					//ARGENT
					/*$rdm = mt_rand(10, 20);
					$money = $this->player->myMoney() + $rdm;
					$this->player->sendActionBarMessage("§7» §fPurifié §e+$rdm ");*/
				}else{
					$player->sendTip("§cMerci de tenir du diamant en main !");
				}
			}

		}
	}

	public static function isPurifZone(Player $player, array $pos, array $pos_)
	{
		if (($player->getPosition()->x >= min($pos[0], $pos_[0])) and ($player->getPosition()->x <= max($pos[0], $pos_[0])) and
			($player->getPosition()->y >= min($pos[1], $pos_[1])) and ($player->getPosition()->y <= max($pos[1], $pos_[1])) and
			($player->getPosition()->z >= min($pos[2], $pos_[2])) and ($player->getPosition()->z <= max($pos[2], $pos_[2]))) {
			return true;
		}
		return false;
	}
}