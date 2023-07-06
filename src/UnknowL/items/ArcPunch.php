<?php

namespace UnknowL\items;

use pocketmine\event\Listener;
use pocketmine\event\player\PlayerItemUseEvent;
use pocketmine\event\player\PlayerJoinEvent;
use pocketmine\event\player\PlayerMoveEvent;
use pocketmine\event\player\PlayerQuitEvent;
use pocketmine\item\enchantment\VanillaEnchantments;
use pocketmine\item\StringToItemParser;
use pocketmine\item\VanillaItems;
use pocketmine\math\Vector3;

class ArcPunch implements Listener
{

    const BOW = 5;

    private $cooldown2 = [];

    /** @var array */
    private $status = [];


    public function join(PlayerJoinEvent $e)
    {
        $p = $e->getPlayer();

        $this->status[$p->getName()] = false;
    }

    public function quit(PlayerQuitEvent $e)
    {
        $p = $e->getPlayer();

        unset($this->status[$p->getName()]);
    }

    public function onBow(PlayerItemUseEvent $event)
    {

        $player = $event->getPlayer();
        $name = $player->getName();
        $item = $event->getPlayer()->getInventory()->getItemInHand();

            if ($item->hasEnchantments()) {
                if ($item->hasEnchantment(VanillaEnchantments::PUNCH())) {
                    if($item->getTypeId() == VanillaItems::BOW()->getTypeId()) {
                        if (!isset($this->cooldown2[$name])) $this->cooldown2[$name] = time();
                        if (time() < $this->cooldown2[$name]) {
                            if ($event->isCancelled()) return;
                            $event->cancel();
                            $second = $this->cooldown2[$name] - time();
                            $player->sendPopup("§d- §fPatientez §d$second §fseconde(s) §d-");
                        } else {
                            $this->cooldown2[$name] = time() + self::BOW;
                            $items = StringToItemParser::getInstance()->parse("arrow");
                            $direction = $player->getDirectionVector();
                            $dx = $direction->getX();
                            $dz = $direction->getZ();

                            if ($player->getInventory()->contains($items)) {
                                $event->cancel();
                                $this->status[$player->getName()] = true;
                            }
                            $player->getInventory()->setItemInHand($item);

                        }
                    }
                }
            }
        }

    public function move(PlayerMoveEvent $e){
        $p = $e->getPlayer();
        $from = $e->getFrom();
        $to = $e->getTo();
        if($this->status[$p->getName()]){
            $distance = 8;
            $x = ($to->x - $from->x) * ($distance / 2);
            $z = ($to->z - $from->z) * ($distance / 2);
            $p->setMotion(new Vector3($x, 0.45, $z));
            $this->status[$p->getName()] = false;
        }
    }
}