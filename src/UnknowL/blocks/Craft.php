<?php

namespace UnknowL\blocks;

use linesia\core\Main;
use pocketmine\crafting\CraftingManagerFromDataHelper;
use pocketmine\crafting\ShapedRecipe;
use pocketmine\crafting\ShapelessRecipe;
use pocketmine\event\inventory\CraftItemEvent;
use pocketmine\event\Listener;
use pocketmine\Server;
use Webmozart\PathUtil\Path;

class Craft implements Listener {

    public function onCraft(CraftItemEvent $event)
    {
        $player = $event->getPlayer();
        $crafts = [
            958 => "casque.use",
            960 => "jambiere.use",
            961 => "botte.use",
            964 => "epee.use"
        ];
        foreach ($event->getOutputs() as $craft){
            $id = $craft->getId();
            if (isset($crafts[$id])){
                $perm = $crafts[$id];
                if (!$player->hasPermission($perm)){
                    $event->cancel();
                    $player->sendMessage("§cTu as doit avoir un certain niveaux dans tes metiers ! Fait /tuto pour en savoir plus.");
                }
            }

        /*$deleteCraft = [
            261 => "craft.use",
			378 => "craft.use",
 			352 => "craft.use",
		    377 => "craft.use",
			353 => "craft.use",
			165 => "craft.use",
			359 => "craft.use",
		 	351 => "craft.use",
			146 => "craft.use",
			362 => "craft.use",
			361 => "craft.use"
        ];
            if (isset($deleteCraft[$id])){
                $perm = $deleteCraft[$id];
                if (!$player->hasPermission($perm)){
                    $event->cancel();
                    $player->sendMessage("§cItem désactivé !");
                }
            }*/
    	}
	}
}