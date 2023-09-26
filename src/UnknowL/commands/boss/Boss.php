<?php

namespace UnknowL\commands\boss;

use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use pocketmine\entity\Location;
use pocketmine\entity\Skin;
use pocketmine\item\Item;
use pocketmine\nbt\tag\CompoundTag;
use pocketmine\player\Player;
use pocketmine\world\World;
use Ramsey\Uuid\Nonstandard\Uuid;
use UnknowL\entities\BossEntity;
use UnknowL\lib\commando\BaseCommand;
use UnknowL\lib\commando\constraint\InGameRequiredConstraint;
use UnknowL\Linesia;

class Boss extends BaseCommand
{
    public function __construct()
    {
        $settings = Linesia::getInstance()->getCommandManager()->getSettings("boss");
        parent::__construct(Linesia::getInstance(), $settings->getName(), $settings->getDescription(),  $settings->getAliases());
    }

	final public function prepare(): void
	{
		$this->setPermission("pocketmine.group.user");
		$this->addConstraint(new InGameRequiredConstraint($this));
	}

	public function onRun(CommandSender $sender, string $aliasUsed, array $args): void
    {
        $config = Linesia::getInstance()->getConfig();
        $bosses = $config->get("boss");
        if ($this->testPermission($sender)) {
            $sender->sendMessage($config->get("no-permission"));
            return;
        } else if (!isset($args[0]) || !isset($args[1])) {
            $sender->sendMessage($config->get("bad-usage"));
            return;
        } else if (!isset($bosses[$boss = $args[0]])) {
            $list = implode(",", array_keys($bosses));
            $sender->sendMessage(str_replace(["{boss}", "{list}"], [$boss, $list], $config->get("boss-does-not-exist")));
            return;
        }

        $position = strtolower($args[1]);
        $data = $bosses[$boss];

        if ($position === "random") {
            $locations = $data["position"];

            if (1 > count($locations)) {
                $sender->sendMessage(str_replace("{boss}", $boss, $config->get("not-have-assigned-position")));
                return;
            }

            list($x, $y, $z, $world) = explode(":", $locations[array_rand($locations)]);
            $world = Linesia::getInstance()->getServer()->getWorldManager()->getWorldByName($world);

            if (!$world instanceof World) {
                $sender->sendMessage($config->get("world-problem"));
                return;
            }

            $location = new Location($x, $y, $z, $world, 0, 0);
        } else if ($position === "here") {
            if (!$sender instanceof Player) {
                $sender->sendMessage($config->get("not-player"));
                return;
            }

            $location = $sender->getLocation();
        } else {
            $sender->sendMessage($config->get("bad-usage"));
            return;
        }

        $skin = $this->getSkinFromName($data["skin"]);

        if ($data["geometry"] === false) {
            $geometry = $this->getGeometryFromName("maxoooz");
        } else {
            $geometry = $this->getGeometryFromName($data["geometry"]);
        }

        $nbt = CompoundTag::create()->setString("boss", $boss);

        $entity = new BossEntity($location, new Skin($skin->getSkinId(), $skin->getSkinData(), "", $geometry[0], $geometry[1]), $nbt);
        $entity->spawnToAll();

        $sender->sendMessage(str_replace("{boss}", $boss, $config->get("succes")));

        if ($data["broadcast"] !== false) {
            Linesia::getInstance()->getServer()->broadcastMessage($data["broadcast"]);
        }
    }

    private function getSkinFromName(string $name): Skin
    {
        $path = Linesia::getInstance()->getDataFolder() . $name;

        if (file_exists($path)) {
            return new Skin(Uuid::uuid4()->toString(), $this->getBytesFromImage($path));
        } else {
            return new Skin(Uuid::uuid4()->toString(), $this->getBytesFromImage("steve.png"));
        }
    }

    private function getBytesFromImage(string $path): string
    {
        $bytes = "";

        $image = imagecreatefrompng($path);
        $size = @getimagesize($path);

        for ($y = 0; $y < $size[1]; $y++) {
            for ($x = 0; $x < $size[0]; $x++) {
                $rgba = @imagecolorat($image, $x, $y);
                $a = ((~(($rgba >> 24))) << 1) & 0xff;
                $r = ($rgba >> 16) & 0xff;
                $g = ($rgba >> 8) & 0xff;
                $b = $rgba & 0xff;
                $bytes .= chr($r) . chr($g) . chr($b) . chr($a);
            }
        }

        @imagedestroy($image);
        return $bytes;
    }

    private function getGeometryFromName(string $name): array
    {
        $path = Linesia::getInstance()->getDataFolder() . $name;

        if (file_exists($path)) {
            return [str_replace(".json", "", $name), file_get_contents($path)];
        } else {
            return ["geometry.humanoid.custom", ""];
        }
    }
}