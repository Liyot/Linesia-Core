<?php

namespace UnknowL\player;

use DaPigGuy\PiggyFactions\PiggyFactions;
use pocketmine\entity\Entity;
use pocketmine\event\entity\EntityDamageEvent;
use pocketmine\inventory\ArmorInventory;
use pocketmine\inventory\CallbackInventoryListener;
use pocketmine\inventory\Inventory;
use pocketmine\item\Armor;
use pocketmine\item\Item;
use pocketmine\item\ItemBlock;
use pocketmine\lang\Translatable;
use pocketmine\math\Vector3;
use pocketmine\nbt\tag\CompoundTag;
use pocketmine\network\mcpe\convert\TypeConverter;
use pocketmine\network\mcpe\protocol\types\BlockPosition;
use pocketmine\network\mcpe\protocol\UpdateBlockPacket;
use pocketmine\player\GameMode;
use pocketmine\player\Player;
use pocketmine\Server;
use pocketmine\utils\Config;
use pocketmine\utils\Utils;
use UnknowL\events\CooldownExpireEvent;
use UnknowL\handlers\dataTypes\PlayerCooldown;
use UnknowL\handlers\dataTypes\Tag;
use UnknowL\handlers\Handler;
use UnknowL\Linesia;
use UnknowL\player\manager\StatManager;
use UnknowL\player\manager\EconomyManager;
use UnknowL\rank\Rank;
use UnknowL\handlers\dataTypes\Cooldown;

final class LinesiaPlayer extends Player
{

	public const INTERACTION_INTERACT = 1;
	public const INTERACTION_BREAK = 2;
	public const INTERACTION_COMMAND = 3;
	public const INTERACTION_ATTACK = 4;
	public const INTERACTION_REQUEST = 5;

	private PlayerProperties $properties;

	private EconomyManager $economyManager;

	private StatManager $statManager;

	private \Closure $onChatResponse;

	private bool $inDual = false, $inCrafting = false;

	/**
	 * @var bool[]
	 */
	private array $activeInteractions = [];

	/***
	 * @var Cooldown[]
	 */
	private array $cooldowns = [];


	private Tag $tag;


	private Rank $rank;

	public function initEntity(CompoundTag $nbt): void
	{
		parent::initEntity($nbt);

		$this->properties = new PlayerProperties($nbt);
		$this->economyManager = new EconomyManager($this);
		$this->statManager = new StatManager($this);
		var_dump($this->properties);

		$this->rank = Handler::RANK()->getRank($this->properties->getProperties("rank"));
		array_map(fn($value) => $this->setBasePermission($value, true), $this->rank->getPermissions());
		$this->properties->setNestedProperties("permissions.normal" ,$this->getRank()->getPermissions());
		$this->removeTempPermissions();

		$this->initTag();

		$this->getArmorInventory()->getListeners()->add(new CallbackInventoryListener(
			function(Inventory $inventory, int $slot, Item $oldItem) : void{
				if ($inventory instanceof ArmorInventory) {
					$targetItem = $inventory->getItem($slot);

					Handler::ARMOREFFECTS()->applyEffect($targetItem, $this);
					Handler::ARMOREFFECTS()->removeEffect($oldItem, $this);
				}
			}
		, function(Inventory $inventory, array $oldContents): void {}));
		$this->loadCooldowns($nbt);
		$this->setBaseActiveInteraction();
		$this->formatNameTag();
	}

	public function saveNBT(): CompoundTag
	{
		$nbt = parent::saveNBT();
		!isset($this->properties) ?: $this->properties->save($nbt);

		return $nbt;
	}

	final public function kill(): void
	{
		$this->getStatManager()->handleEvents(StatManager::TYPE_DEATH);
		parent::kill();
	}

	final public function attackEntity(Entity $entity): bool
	{
		$result = parent::attackEntity($entity);
		if ($entity instanceof LinesiaPlayer && !$entity->isAlive())
		{
			$this->getStatManager()->handleEvents(StatManager::TYPE_KILL);
		}
		return $result;
	}

	final public function breakBlock(Vector3 $pos): bool
	{
		$result = parent::breakBlock($pos);
		if ($result) $this->getStatManager()->handleEvents(StatManager::TYPE_BLOCK_MINED);
		return $result;
	}

	final public function hasPermission($name): bool
	{
		$perm = $this->getPlayerProperties()->getProperties("permissions");
		var_dump($perm);
		return in_array($name, !isset($this->rank) ? [] : $this->getRank()->getPermissions(), true)
            || parent::hasPermission($name)
            || Server::getInstance()->isOp($this->getName())
			|| in_array($name, is_array($perm["normal"]) ? $perm["normal"] : [$perm["temp"]])
			|| in_array($name, is_array($perm["temp"]) ? $perm["temp"] : [$perm["temp"]]);

	}

	final public function addPermission(string $perm, int $days = 0): void
	{
        if(!empty($days))
        {
            $permissions = $this->getPlayerProperties()->getProperties("permissions.temp");
			is_array($permissions) ?: $permissions = [$permissions];
			$permissions[$days] = $perm;
            $this->getPlayerProperties()->setNestedProperties("permissions.temp", $permissions);
            return;
        }
		$permissions = $this->getPlayerProperties()->getNestedProperties("permissions");
		is_array($permissions) ?: $permissions = [$permissions];
		$permissions[] = $perm;
		$this->getPlayerProperties()->setNestedProperties("permissions.normal", $perm);
	}

	final public function addCooldown(PlayerCooldown $cooldown, string $path): void
	{
		$this->cooldowns[$path] = $cooldown;
	}

	final public function getCooldown(string $path): ?Cooldown
	{
		return $this->cooldowns[$path] ?? null;
	}

	final public function loadCooldowns(CompoundTag $tag, string $previous = ""): void
	{
		foreach ($tag->getValue() as $key => $properties)
		{
			if (str_contains($key, "cooldown"))
			{
				if(!empty($properties))
				{
					$path = empty($previous) ? $key : $previous.'.'.$key;
					var_dump([$path => (int)$properties->getValue()]);
					$previous = "";
					$cooldown = new PlayerCooldown((int)$properties->getValue(), $this, $path, true);
					$this->addCooldown($cooldown, $path);
					$this->testCooldown($path);
				}
				continue;
			}elseif($properties instanceof CompoundTag)
			{
				$this->loadCooldowns($properties, $key);
			}
		}
	}

    protected function testCooldown(string $path): bool
    {
        $cooldown = $this->getCooldown($path);
        if(!is_null($cooldown))
        {
            if($cooldown->getCooldownTime() <= time())
            {
                $event = new CooldownExpireEvent($cooldown, $this);
                $event->call();
                return true;
            }
            return false;
        }
        return true;
    }

	final public function move(float $dx, float $dy, float $dz): void
	{
		if ($this->inCrafting) $this->removeCraftingInventory();
		parent::move($dx, $dy, $dz);
	}

	final public function onUpdate(int $currentTick): bool
	{
		$this->setScoreTag($this->formatHealth());

		if ($currentTick % (20 * 60) === 0) $this->getStatManager()->recalculateGameTime();

		return parent::onUpdate($currentTick);
	}

	public function formatNameTag(): void
	{
		$this->setNameTag($this->rank->getNametag($this));
	}

	private function formatHealth(): string
	{
		return str_repeat("§a■", round($this->getHealth() / 2)) . str_repeat("§c■", round(($this->getMaxHealth() - $this->getHealth()) / 2));
	}

	private function removeTempPermissions(): void
	{
		$tempPermissions = $this->getPlayerProperties()->getNestedProperties("permissions.temp");
		foreach ($tempPermissions ?? [] as $key => $value)
		{
			if ($value < time())
			{
				unset($tempPermissions[$key]);
			}
		}
		$this->getPlayerProperties()->setNestedProperties("permissions.temp", $tempPermissions ?? []);
	}

	final public function onPostDisconnect(Translatable|string $reason, Translatable|string|null $quitMessage): void
	{
		foreach ($this->cooldowns as $cooldown)
		{
			$cooldown->save($this);
		}
		$this->properties->setProperties('activeTags', $this->tag->getName());
		$this->properties->setProperties('rank', $this->rank->getName());
		$this->properties->setProperties('money', $this->getEconomyManager()->getMoney());
		$this->saveManager();
		parent::onPostDisconnect($reason, $quitMessage); // TODO: Change the autogenerated stub
	}

	final public function awaitChatResponse(\Closure $function, bool $check = true, $args = []): bool
	{
		if(!isset($this->onChatResponse))
		{
			if (!$check)
			{
				Utils::validateCallableSignature(function (LinesiaPlayer $player, mixed $response) { }, $this->onChatResponse);
				$this->onChatResponse = \Closure::fromCallable($function);
				return false;
			}
			return false;
		}
		$this->onChatResponse->call($this->onChatResponse, $this, $args);
		return true;
	}

	final public function saveManager(): void
	{
		$this->economyManager->save();
		$this->statManager->save();
	}

	final public function setInDual(bool $value = true): void
	{
		$this->inDual = $value;
	}

	final public function isInDual(): bool
	{
		return $this->inDual;
	}

	final public function initTag(): void
	{
		if ($this->getPlayerProperties()->getProperties('activeTag') !== null)
		{
			$this->tag = Handler::TAG()->getTag($this->getPlayerProperties()->getProperties('activeTag'));
			return;
		}
		$this->tag = Handler::TAG()->getTag("Joueur");
	}

	final public function attack(EntityDamageEvent $source): void
	{
		parent::attack($source);
		$this->setScoreTag($this->formatHealth());
	}

	final public function setBaseActiveInteraction(): void
	{
		$this->setActiveInteraction(self::INTERACTION_ATTACK, true);
		$this->setActiveInteraction(self::INTERACTION_INTERACT, true);
		$this->setActiveInteraction(self::INTERACTION_COMMAND, true);
		$this->setActiveInteraction(self::INTERACTION_BREAK, true);
		$this->setActiveInteraction(self::INTERACTION_REQUEST, true);
	}

	/**
	 * @param int $interaction
	 * @return bool
	 */
	final public function getActiveInteraction(int $interaction): bool
	{
		return $this->activeInteractions[$interaction];
	}

	final public function setActiveInteraction(int $interaction, bool $value): void
	{
		$this->activeInteractions[$interaction] = $value;
	}



	final public function getEconomyManager(): EconomyManager
	{
		return $this->economyManager;
	}

	final public function getPlayerProperties(): PlayerProperties
	{
		return $this->properties;
	}

	final public function getStatManager(): StatManager
	{
		return $this->statManager;
	}

	/**
	 * @return Rank
	 */
	final public function getRank(): Rank
	{
		return $this->rank;
	}

	final public function setRank(Rank $rank): self
	{
		$this->rank = $rank;
		return $this;
	}

	/**
	 * @return Tag
	 */
	final public function getTag(): Tag
	{
		return $this->tag;
	}

	/**
	 * @param Tag $tag
	 */
	final public function setTag(Tag $tag): void
	{
		if ($this->hasPermission($tag->getPermission())) $this->tag = $tag;
	}

	final public function hasTag(string $tag): bool
	{
		$tag = Handler::TAG()->getTag($tag);
		return $this->hasPermission($tag->getPermission());
	}

	private function removeCraftingInventory(): void
	{
		$this->getNetworkSession()->sendDataPacket(UpdateBlockPacket::create
		(
			BlockPosition::fromVector3($this->getPosition()->add(0, 3, 0)),
			TypeConverter::getInstance()->getBlockTranslator()->internalIdToNetworkId($this->getWorld()->getBlock($this->getPosition()->add(0, 3, 0))->getStateId()),
			UpdateBlockPacket::FLAG_NETWORK,
			UpdateBlockPacket::DATA_LAYER_NORMAL)
		);
		$this->setInCrafting(false);
	}

	/**
	 * @return bool
	 */
	public function isInCrafting(): bool
	{
		return $this->inCrafting;
	}

	/**
	 * @param bool $inCrafting
	 */
	public function setInCrafting(bool $inCrafting = true): void
	{
		$this->inCrafting = $inCrafting;
	}
}