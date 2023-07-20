<?php

namespace UnknowL\entities;

use jojoe77777\FormAPI\ModalForm;
use JsonException;
use UnknowL\api\MineAPI;
use UnknowL\lib\ref\libNpcDialogue\form\NpcDialogueButtonData;
use UnknowL\lib\ref\libNpcDialogue\NpcDialogue;
use pocketmine\entity\Location;
use pocketmine\entity\Villager;
use pocketmine\event\entity\EntityDamageByEntityEvent;
use pocketmine\event\entity\EntityDamageEvent;
use pocketmine\item\VanillaItems;
use pocketmine\math\Vector3;
use pocketmine\nbt\tag\CompoundTag;
use pocketmine\permission\DefaultPermissions;
use pocketmine\player\Player;
use UnknowL\Linesia;
use UnknowL\player\LinesiaPlayer;

final class MinageNPC extends Villager
{
    public function __construct(Location $location, ?CompoundTag $nbt = null)
    {
        parent::__construct($location, $nbt);
        $this->setNoClientPredictions(true);
        $this->setNameTagAlwaysVisible(true);
		$this->setNametagVisible(true);
		$this->setNametag("Gerard");
    }

    /**
     * @throws JsonException
     */
    public function onInteract(Player $player, Vector3 $clickPos): bool
    {
        $quest = match (MineAPI::getInstance()->getQuestData()->getNested($player->getName() . ".quest", 1)) {
            1 => "Quest1",
            2 => "Quest2",
            3 => "Quest3",
            4 => "Quest4",
            5 => "Quest5",
            default => "FINISH"
        };
        $npcDialogue = new NpcDialogue();
        $npcDialogue->addButton(NpcDialogueButtonData::create()
            ->setName($quest !== 'FINISH' ? "Faire la quête" : "OK !")
            ->setClickHandler(function (Player $player) use ($quest): void {
                if ($this->hasRequirements($player, $quest)) {
                    $this->nextQuest($player, $quest);
                } else {
                    $player->sendMessage("§cVous ne m'avez pas ramené ce que je vous ai demandé !");
                }
            })
            ->setForceCloseOnClick(true)
        );
        $npcDialogue->setNpcName("Minage NPC");
        $npcDialogue->setDialogueBody($this->getQuest($player, $quest));
        $npcDialogue->setSceneName($quest);
        $op = false;
        if ($player->hasPermission(DefaultPermissions::ROOT_OPERATOR)) {
            $player->setBasePermission(DefaultPermissions::ROOT_OPERATOR, false);
            $op = true;
        }
        $npcDialogue->sendTo($player, $this);
        if ($op) {
            $player->setBasePermission(DefaultPermissions::ROOT_OPERATOR, true);
        }
        return true;
    }

    /**
     * @throws JsonException
     */
    public function nextQuest(Player $player, string $quest): void
    {
        switch ($quest){
            case "Quest1":
                $player->getInventory()->removeItem(VanillaItems::EMERALD()->setCount(192));
                $player->sendMessage("§aVous avez terminé la première quête !");
                MineAPI::getInstance()->getQuestData()->setNested($player->getName() . ".quest", 2);
				MineAPI::getInstance()->getQuestData()->save();
                break;
            case "Quest2":
                $player->getInventory()->removeItem(VanillaItems::IRON_NUGGET()->setCount(5));
                $player->sendMessage("§aVous avez terminé la deuxième quête !");
				MineAPI::getInstance()->getQuestData()->setNested($player->getName() . ".quest", 3);
				MineAPI::getInstance()->getQuestData()->save();
                break;
            case "Quest3":
                $player->getInventory()->removeItem(VanillaItems::GOLD_INGOT()->setCount(256)); //128 rubis
                $player->sendMessage("§aVous avez terminé la troisième quête !");
				MineAPI::getInstance()->getQuestData()->setNested($player->getName() . ".quest", 4);
				MineAPI::getInstance()->getQuestData()->save();
                break;
            case "Quest4":
                $player->getInventory()->removeItem(VanillaItems::ROTTEN_FLESH()->setCount(2000));
                $player->sendMessage("§aVous avez terminé la quatrième quête !");
				MineAPI::getInstance()->getQuestData()->setNested($player->getName() . ".quest", 5);
				MineAPI::getInstance()->getQuestData()->save();
                break;
            case "Quest5":
                $player->getEconomyManager()->reduce(200000);
                $player->sendMessage("§aVous avez terminé la dernière quête !");
				MineAPI::getInstance()->getQuestData()->setNested($player->getName() . ".quest", 6);
				MineAPI::getInstance()->getQuestData()->save();
                break;
        }
    }

    public function hasRequirements(Player $player, string $quest): bool
    {
        $countItems = function (int $id) use ($player): int {
            $count = 0;
            foreach ($player->getInventory()->getContents() as $item) {
                if ($item->getTypeId() === $id) {
                    $count += $item->getCount();
                }
            }
            return $count;
        };
        return match ($quest) {
            "Quest1" => $countItems(VanillaItems::EMERALD()->getTypeId()) >= 192,
            "Quest2" => $countItems(VanillaItems::IRON_NUGGET()->getTypeId()) >= 5,
            "Quest3" => $countItems(VanillaItems::GOLD_INGOT()->getTypeId()) >= 256,
            "Quest4" => $countItems(VanillaItems::ROTTEN_FLESH()->getTypeId()) >= 2000,
            "Quest5" => $player->getEconomyManager()->getMoney() >= 200000,
            default => true,
        };
    }

    public function getQuest(Player $player, string $quest): string
    {
        return match ($quest) {
			"Quest1" => "Salutations, jeune aventurier ! Si tu cherches à accéder à la Farmzone Enfer, tu es au bon endroit. Toutefois, tu devras effectuer 5 quêtes pour y parvenir. La première consiste à me rapporter §d192 diamants purifiés§f.",
			"Quest2" => "Pour la deuxième quête, tu devras me ramener §d5 fragements d'onix§f.",
			"Quest3" => "Pour la troisième quête, tu devras me rapporter §d256 lingots de rubis§f.",
			"Quest4" => "La quatrième quête te demandera de me fournir §d2000 chairs putréfiées§f.",
			"Quest5" => "Enfin, pour la cinquième et dernière quête, il te faudra me donner §d200 000 de money§f. Si tu réussis cette quête, tu auras accès à la Farmzone Enfer.",
            default => "§cVous avez déjà terminé toutes les quêtes."
        };
    }

    public function attack(EntityDamageEvent $source): void
    {
        if($source instanceof EntityDamageByEntityEvent){
            $damager = $source->getDamager();
            if($damager instanceof Player and $damager->hasPermission(DefaultPermissions::ROOT_OPERATOR) and $damager->isSneaking()){
                $form = new ModalForm(function(Player $player, $data): void {
                    if($data){
                        $this->flagForDespawn();
                    }
                });
                $form->setTitle("Supprimer le NPC ?");
                $form->setContent("Voulez-vous vraiment §csupprimer §fce NPC ?");
                $form->setButton1("§aOui");
                $form->setButton2("§cNon");
                $damager->sendForm($form);
            }
        }
    }


}