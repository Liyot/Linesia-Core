<?php

namespace UnknowL\api;

use CortexPE\DiscordWebhookAPI\Embed;
use CortexPE\DiscordWebhookAPI\Message;
use pocketmine\color\Color;
use pocketmine\event\inventory\InventoryTransactionEvent;
use pocketmine\event\Listener;
use pocketmine\event\server\DataPacketReceiveEvent;
use pocketmine\network\mcpe\protocol\InventoryTransactionPacket;
use pocketmine\network\mcpe\protocol\LevelSoundEventPacket;
use pocketmine\network\mcpe\protocol\PlayerActionPacket;
use pocketmine\network\mcpe\protocol\types\inventory\UseItemOnEntityTransactionData;
use pocketmine\network\mcpe\protocol\types\LevelSoundEvent;
use pocketmine\network\mcpe\protocol\types\PlayerAction;
use pocketmine\network\mcpe\protocol\AnimatePacket;
use pocketmine\permission\DefaultPermissions;
use pocketmine\player\Player;
use pocketmine\Server;

class CpsAPI implements Listener {

    protected $countLeftClickBlock;
    protected array $clickData = [];
    protected array $cps = [];
    protected int $cooldown = 2;
    protected array $timer = [];

    public function addClick(Player $player)
    {
        if(!isset(Main::$instance->clicks[$player->getName()])) return;
        array_unshift(Main::$instance->clicks[$player->getName()], microtime(true));
        if (count(Main::$instance->clicks[$player->getName()]) > 30)
            array_pop(Main::$instance->clicks[$player->getName()]);
        /*if($this->getCps($player) > 20) {
            foreach(Server::getInstance()->getOnlinePlayers() as $players) {
                if($players instanceof Player) {
                    if($players->hasPermission("cps.use") or $players->hasPermission(DefaultPermissions::ROOT_OPERATOR)) {
                        if(!isset($this->timer[$player->getName()]) or time() > $this->timer[$players->getName()]) {
                            $this->timer[$players->getName()] = time() + $this->cooldown;
                            $players->sendMessage("§d{$player->getName()} §ffait actuellement §d{$this->getCps($player)} cps §7({$player->getNetworkSession()->getPing()} ms)");

                            /*$msg = new Message();
                            $embed = new Embed();
                            $embed->setTitle("Alerte CPS");
                            $embed->setColor(0x00FF00);
                            $embed->setDescription(
                                "**__Utilisateur:__** `{$player->getName()}`\n" .
                                "**__Ping:__** `{$player->getNetworkSession()->getPing()}`\n".
                                "**__Cps:__** `{$this->getCps($player)}`"
                            );
                            $embed->setTimestamp(new \DateTime());
                            $embed->setFooter("CoreLinesia");
                            $msg->addEmbed($embed);
        					$webhook = new Webhook("https://discord.com/api/webhooks/1051920192118210673/7KBoAOmmfKWZUaQf76klH3zPq_dNHPGK6Y3uSBwwgiV5D03djmIh3FsbiMQFEtWy0R8S");
                            $webhook->send($msg);*/
                        /*}
                    }
                }
            }
        }*/

        if (SettingsAPI::isEnableSettings($player, "cps")) {
            $player->sendTip("§l§5> §r§f" . abs($this->getCps($player)));
        }
    }

    public function getCps(Player $player, float $deltaTime = 1.0, int $roundPrecision = 1):int
    {
        $mt = microtime(true);
        return round(count(array_filter(Main::$instance->clicks[$player->getName()], static function (float $t) use ($deltaTime, $mt): bool {
                return ($mt - $t) <= 1;
            })) / $deltaTime, $roundPrecision);
    }

    public function onDataReceive(DataPacketReceiveEvent $event):void
    {
        $packet = $event->getPacket();
        if ($packet::NETWORK_ID === LevelSoundEventPacket::NETWORK_ID and $packet instanceof LevelSoundEventPacket)
        {
            $player = $event->getOrigin()->getPlayer();
            if($player instanceof Player)
            {
                if ($packet->sound === LevelSoundEvent::ATTACK_NODAMAGE or $packet->sound === LevelSoundEvent::ATTACK_STRONG)
                {
                    $this->addClick($player);
                }
            }
        }

        //ANIMATION
        if ($packet instanceof AnimatePacket) {
            $event->getOrigin()->getPlayer()->getServer()->broadcastPackets($event->getOrigin()->getPlayer()->getViewers(), [$event->getPacket()]);
        }
    }
}