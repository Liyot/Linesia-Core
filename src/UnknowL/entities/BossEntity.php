<?php

namespace UnknowL\entities;

use DaPigGuy\PiggyFactions\claims\Claim;
use DaPigGuy\PiggyFactions\PiggyFactions;
use pocketmine\data\bedrock\EnchantmentIdMap;
use pocketmine\entity\animation\ArmSwingAnimation;
use pocketmine\entity\Entity;
use pocketmine\entity\Human;
use pocketmine\entity\Location;
use pocketmine\entity\Skin;
use pocketmine\event\entity\EntityDamageByEntityEvent;
use pocketmine\event\entity\EntityDamageEvent;
use pocketmine\event\entity\EntityDeathEvent;
use pocketmine\item\enchantment\EnchantmentInstance;
use pocketmine\item\StringToItemParser;
use pocketmine\math\Vector3;
use pocketmine\nbt\tag\CompoundTag;
use pocketmine\network\mcpe\NetworkBroadcastUtils;
use pocketmine\network\mcpe\protocol\AddActorPacket;
use pocketmine\network\mcpe\protocol\PlaySoundPacket;
use pocketmine\network\mcpe\protocol\types\entity\PropertySyncData;
use pocketmine\player\Player;
use pocketmine\Server;
use pocketmine\utils\Config;
use pocketmine\world\format\io\GlobalItemDataHandlers;
use pocketmine\world\particle\BlockBreakParticle;
use pocketmine\world\Position;
use UnknowL\Linesia;

class BossEntity extends Human
{
    public float $speed;
    public int $attackWait;

    private ?Player $target = null;
    private Position $lastPosition;

    private Config $config;

    private int $findNewTargetTicks = 0;
    private int $missedHit = 0;

    private array $data;

    public function __construct(Location $location, Skin $skin, ?CompoundTag $nbt = null)
    {
        $type = $nbt->getString("boss");

        $this->config = Linesia::getInstance()->getConfig();
        $this->data = $this->config->get("boss")[$type];

        $this->speed = $this->data["speed"];
        $this->attackWait = $this->data["attack-wait"];

        parent::__construct($location, $skin, $nbt);
    }

    public function entityBaseTick(int $tickDiff = 1): bool
    {
        if ($this->insideSafeZone($this->getPosition())) {
            $this->teleportLastPosition();
        }

        $this->lastPosition = $this->getPosition();
        parent::entityBaseTick($tickDiff);

        if (!$this->isAlive()) {
            if (!$this->closed) {
                $this->flagForDespawn();
            }
            return false;
        }

        if ($this->target instanceof Player) {
            return $this->attackTarget();
        } else if ($this->findNewTargetTicks > 0) {
            $this->findNewTargetTicks--;
        } else if ($this->findNewTargetTicks === 0) {
            $this->findNewTarget();
        }

        if (!$this->isOnGround()) {
            if ($this->motion->y > -$this->gravity * 4) {
                $this->motion->y = -$this->gravity * 4;
            } else {
                $this->motion->y += $this->isUnderwater() ? $this->gravity : -$this->gravity;
            }
        } else {
            $this->motion->y -= $this->gravity;
        }

        $this->move($this->motion->x, $this->motion->y, $this->motion->z);
        $this->updateMovement();

        return $this->isAlive();
    }

    private function insideSafeZone(Position $position): bool
    {
        $config = $this->config;

        if ($config->get("claim-is-safe-area")) {
            $plugin = Linesia::getInstance()->getServer()->getPluginManager()->getPlugin("PiggyFactions");

            if (!is_null($plugin)) {
                $claim = PiggyFactions::getInstance()->getClaimsManager()->getClaimByPosition($position);

                if ($claim instanceof Claim) {
                    return true;
                }
            }
        }

        foreach ($config->get("safe-area") as $data) {
            $pos1 = explode(":", $data["pos1"]);
            $pos2 = explode(":", $data["pos2"]);

            $minX = min(intval($pos1[0]), intval($pos2[0]));
            $minY = min(intval($pos1[1]), intval($pos2[1]));
            $minZ = min(intval($pos1[2]), intval($pos2[2]));

            $maxX = max(intval($pos1[0]), intval($pos2[0]));
            $maxY = max(intval($pos1[1]), intval($pos2[1]));
            $maxZ = max(intval($pos1[2]), intval($pos2[2]));

            $x = $position->getFloorX();
            $y = $position->getFloorY();
            $z = $position->getFloorZ();

            if ($x >= $minX && $x <= $maxX && $y >= $minY && $y <= $maxY && $z >= $minZ && $z <= $maxZ && $position->getWorld()->getFolderName() === $data["world"]) {
                return true;
            }
        }
        return false;
    }

    public function teleportLastPosition(): void
    {
        $this->setPosition($this->lastPosition);
        $this->updateMovement();
    }

    public function attackTarget(): bool
    {
        $target = $this->target;

        if (!$this->checkTarget($target)) {
            $this->target = null;
            $this->missedHit = 0;
            return true;
        }

        if (!$this->isOnGround()) {
            if ($this->motion->y > -$this->gravity * 4) {
                $this->motion->y = -$this->gravity * 4;
            } else {
                $this->motion->y += $this->isUnderwater() ? $this->gravity : -$this->gravity;
            }
        } else {
            $this->motion->y -= $this->gravity;
        }

        $this->move($this->motion->x, $this->motion->y, $this->motion->z);

        $x = $target->getPosition()->x - $this->getPosition()->x;
        $y = $target->getPosition()->y - $this->getPosition()->y;
        $z = $target->getPosition()->z - $this->getPosition()->z;

        if ($x * $x + $z * $z < 1.2) {
            $this->motion->x = 0;
            $this->motion->z = 0;
        } else {
            $this->motion->x = ($this->isUnderwater() ? $this->speed / 2 : $this->speed) * 0.15 * ($x / (abs($x) + abs($z)));
            $this->motion->z = ($this->isUnderwater() ? $this->speed / 2 : $this->speed) * 0.15 * ($z / (abs($x) + abs($z)));
        }

        $angle = atan2($z, $x);
        $yaw = (($angle * 180) / M_PI) - 90;

        $dist = $this->getPosition()->distance($target->getPosition());

        $angle = atan2($dist, $y);
        $pitch = (($angle * 180) / M_PI) - 60;

        $this->location->yaw = $yaw;
        $this->location->pitch = $pitch;

        $this->move($this->motion->x, $this->motion->y, $this->motion->z);

        if ($this->getPosition()->distance($target->getPosition()) <= $this->data["reach"] && $this->attackWait <= 0) {
            $this->broadcastAnimation(new ArmSwingAnimation($this), $this->getViewers());

            $ev = new EntityDamageByEntityEvent($this, $target, EntityDamageEvent::CAUSE_ENTITY_ATTACK, $this->data["damage"]);
            $target->attack($ev);

            $this->attackWait = $this->data["attack-wait"];
            $this->missedHit = 0;

            $luck = $this->data["lightning"];

            if ($luck !== false && mt_rand(1, $luck) === 1) {
                $this->makeLightning($target);
            }
        } else {
            $this->missedHit++;
        }

        $this->updateMovement();
        $this->attackWait--;

        return $this->isAlive();
    }

    private function checkTarget(?Player $target): bool
    {
        return
            $target instanceof Player &&
            $target->isAlive() &&
            !$target->isCreative() &&
            $this->data["target-max-distance"] > $target->getPosition()->distance($this->getPosition()) &&
            !$this->insideSafeZone($target->getPosition()) &&
            $this->data["missed-hit-change"] > $this->missedHit;
    }

    public function attack(EntityDamageEvent $source): void
    {
        $this->updateNameTag();

        if ($source instanceof EntityDamageByEntityEvent) {
            $damager = $source->getDamager();

            if ($damager instanceof Player) {
                if (is_null($this->target) && $this->checkTarget($damager)) {
                    $this->target = $damager;
                    $this->missedHit = 0;

                    $source->setKnockBack($this->data["knockback"]);
                }
            }
        }

        parent::attack($source);
    }

    private function makeLightning(Entity $victim): void
    {
        $location = $victim->getLocation();
        $world = $location->getWorld();

        $light = new AddActorPacket();
        $light->actorUniqueId = Entity::nextRuntimeId();

        $light->actorRuntimeId = 1;
        $light->position = $location->asVector3();
        $light->type = "minecraft:lightning_bolt";
        $light->yaw = $location->getYaw();
        $light->syncedProperties = new PropertySyncData([], []);

        $world->addParticle($location, new BlockBreakParticle($world->getBlock($location->floor()->down())), $world->getPlayers());

        NetworkBroadcastUtils::broadcastPackets($world->getPlayers(), [
            $light,
            PlaySoundPacket::create("ambient.weather.thunder", $location->getX(), $location->getY(), $location->getZ(), 1, 1)
        ]);
    }

    public function findNewTarget(): void
    {
        $distance = $this->data["max-distance"];
        $target = null;

        foreach ($this->getWorld()->getPlayers() as $player) {
            if ($player instanceof Player && $distance >= $player->getPosition()->distance($this->getPosition()) && $this->checkTarget($player)) {
                $distance = $player->getPosition()->distance($this->getPosition());
                $target = $player;
            }
        }

        $this->findNewTargetTicks = $this->data["search-new-target"];
        $this->target = (!is_null($target) ? $target : null);

        $this->missedHit = 0;
    }

    protected function onDeath(): void
    {
        $ev = new EntityDeathEvent($this, $this->getDrops(), $this->getXpDropAmount());
        $ev->call();

        if ($this->data["drops"]["explosion"]) {
            $motion = (new Vector3(mt_rand(-5, 5), 10, mt_rand(-5, 5)))->normalize();
        } else {
            $motion = null;
        }

        foreach ($ev->getDrops() as $item) {
            $this->getWorld()->dropItem($this->location, $item, $motion);
        }

        $this->getWorld()->dropExperience($this->location, $ev->getXpDropAmount());
        $this->startDeathAnimation();
    }

    public function getDrops(): array
    {
        $drops = [];

        foreach ($this->data["drops"]["items"] as $item) {
            $reward = explode(":", $item);
            GlobalItemDataHandlers::getUpgrader()->upgradeItemTypeDataInt(intval($reward[0]), intval($reward[1]), intval($reward[2]), null);

            if (isset($reward[3]) && $reward[3] !== "") {
                $item = $item->setCustomName($reward[3]);
            }

            if (isset($reward[4]) && $reward[4] !== "") {
                foreach (explode(";", $reward[4]) as $enchant) {
                    $enchant = explode(",", $enchant);

                    $enchant = new EnchantmentInstance(
                        EnchantmentIdMap::getInstance()->fromId(intval($enchant[0])),
                        intval($enchant[1])
                    );

                    $item = $item->addEnchantment($enchant);
                }
            }

            $drops[] = $item;
        }

        return $drops;
    }

    public function getXpDropAmount(): int
    {
        $this->getXpManager()->setXpLevel($this->data["drops"]["xp"]);
        return parent::getXpDropAmount();
    }

    protected function initEntity(CompoundTag $nbt): void
    {
        $this->setHealth($this->getMaxHealth());
        $this->setScale($this->data["size"]);

        $this->updateNameTag();
        parent::initEntity($nbt);
    }

    public function getMaxHealth(): int
    {
        return $this->data["health"];
    }

    private function updateNameTag(): void
    {
        $nametag = $this->data["nametag"];
        $scoretag = $this->data["scoretag"];

        $this->setNameTag(str_replace(
            ["{health}", "{health_bar}", "{line}"],
            [round($this->getHealth(), 2), $this->getHealthBar(), "\n"],
            $nametag
        ));

        if ($scoretag !== false) {
            $this->setScoreTag(str_replace(
                ["{health}", "{health_bar}", "{line}"],
                [round($this->getHealth(), 2), $this->getHealthBar(), "\n"],
                $scoretag
            ));
        }

        $this->setNameTagAlwaysVisible();
    }

    private function getHealthBar(): string
    {
        $length = intval($this->data["health-bar-length"]);
        $progress = round((($this->getHealth() / $this->getMaxHealth()) * $length));

        return "§a" . str_repeat("|", $progress) . "§c" . str_repeat("|", $length - $progress);
    }
}