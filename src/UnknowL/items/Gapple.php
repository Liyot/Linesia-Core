<?php

namespace UnknowL\items;

use pocketmine\entity\effect\EffectInstance;
use pocketmine\entity\effect\VanillaEffects;
use pocketmine\event\Listener;
use pocketmine\event\player\PlayerItemConsumeEvent;
use pocketmine\item\VanillaItems;

class Gapple implements Listener
{

    const TYPE_GAPPLE = 0;
    const TYPE_CHORUS = 0;
    private $cooldown = [];

    public function gapple(PlayerItemConsumeEvent $event)
    {
        if ($event->getPlayer()->getInventory()->getItemInHand()->getTypeId() == VanillaItems::GOLDEN_APPLE()->getTypeId()) {
            $absorption = new EffectInstance(VanillaEffects::ABSORPTION(), 20 * 180, 0);
            $reg = new EffectInstance(VanillaEffects::REGENERATION(), 20 * 4, 1);
            $event->getPlayer()->getEffects()->add($absorption);
            $event->getPlayer()->getEffects()->add($reg);
        }

        if ($event->getPlayer()->getInventory()->getItemInHand()->getTypeId() == VanillaItems::ENCHANTED_GOLDEN_APPLE()->getTypeId()) {
            $absorption = new EffectInstance(VanillaEffects::ABSORPTION(), 20 * 180, 2);
            $reg = new EffectInstance(VanillaEffects::REGENERATION(), 20 * 15, 2);
            $res = new EffectInstance(VanillaEffects::RESISTANCE(), 20 * 60, 0);
            $event->getPlayer()->getEffects()->add($absorption);
            $event->getPlayer()->getEffects()->add($res);
            $event->getPlayer()->getEffects()->add($reg);
        }

        if ($event->getPlayer()->getInventory()->getItemInHand()->getTypeId() == VanillaItems::CHORUS_FRUIT()->getTypeId()) {
            $lastPlayerTime = $this->cooldown[$event->getPlayer()->getName()][$this::TYPE_CHORUS] ?? 0;
            $timeNow = time();
            $cooldown = $timeNow - $lastPlayerTime;
            $timerest = 120 - $cooldown;
            if ($cooldown >= 120) {
                $this->cooldown[$event->getPlayer()->getName()][$this::TYPE_CHORUS] = $timeNow;
            } else {
                $event->cancel();
                $event->getPlayer()->sendMessage("§cLa prochain utilisation de votre chorus est dans ${timerest}s");
            }
        }
    }
}