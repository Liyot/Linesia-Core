<?php

namespace UnknowL\player;

use pocketmine\entity\Entity;
use pocketmine\form\Form;
use pocketmine\lang\Translatable;
use pocketmine\math\Vector3;
use pocketmine\nbt\tag\CompoundTag;
use pocketmine\nbt\tag\ListTag;
use pocketmine\player\Player;
use pocketmine\scheduler\ClosureTask;
use pocketmine\Server;
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

	private bool $inDual = false;

	/**
	 * @var bool[]
	 */
	private array $activeInteractions = [];

	/***
	 * @var Cooldown[]
	 */
	private array $cooldowns = [];

	/**@var string[]*/
	private array $purchasedTags = [];

	private Tag $tag;


	private Rank $rank;

	public function initEntity(CompoundTag $nbt): void
	{
		parent::initEntity($nbt);

		$this->properties = new PlayerProperties($nbt);
		$this->economyManager = new EconomyManager($this);
		$this->statManager = new StatManager($this);

		$this->rank = Handler::RANK()->getRank($this->properties->getProperties("rank"));
		array_map(fn($value) => $this->setBasePermission($value, true), $this->rank->getPermissions());
		$this->properties->setProperties("permissions" ,$this->getRank()->getPermissions());

		$this->purchasedTags = $this->properties->getProperties("purchasedTags") ?? [];

		$this->loadCooldowns($nbt);
		$this->setBaseActiveInteraction();
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
		return in_array($name, !isset($this->rank) ? [] : $this->getRank()->getPermissions(), true)
            || parent::hasPermission($name)
            || Server::getInstance()->isOp($this->getName());
	}

	final public function addPermission(string $perm, string $days = ""): void
	{
        if(!empty($days))
        {
            $permissions = $this->getPlayerProperties()->getNestedProperties("permissons.temp");
            $permissions[] = [$perm, $days];
            $this->getPlayerProperties()->setNestedProperties("permissions.temp", $permissions);
            return;
        }
		$permissions = $this->getPlayerProperties()->getProperties("permissions");
		$permissions[] = $perm;
		$this->getPlayerProperties()->setProperties("permissions", $perm);
	}

	final public function addCooldown(PlayerCooldown $cooldown, string $path, ...$args): void
	{
		$this->cooldowns[sprintf($path, $args)] = $cooldown;
	}

	final public function getCooldown(string $path, $args): ?Cooldown
	{
		return $this->cooldowns[sprintf($path, $args)] ?? null;
	}

	final public function loadCooldowns(CompoundTag|ListTag $array, $path = ""): void
	{
		foreach ($array->getValue() as $key => $properties)
		{
			if($key === "cooldown" && $properties->getValue() !== "'null'")
			{
				$data = explode(";", $properties->getValue());
				$cooldown = new PlayerCooldown(\DateTime::createFromFormat("d:H:i:s", $data[0]), $this, $data[1], true,
					\DateTime::createFromFormat("d:H:i:s",$data[2]));
				$this->addCooldown($cooldown,empty($path) ? $key : $path);
                $this->testCooldown(empty($path) ? $key : $path);
				return;
			}

			if($properties instanceof CompoundTag || $properties instanceof ListTag)
			{
				$this->loadCooldowns($properties, $path.$key);
			}
		}
	}

    protected function testCooldown(string $path): bool
    {
        $cooldown = $this->getCooldown($path, strtolower($this->getName()));
        if(!is_null($cooldown))
        {
            if($cooldown->end())
            {
                $event = new CooldownExpireEvent($cooldown, $this);
                $event->call();
                return true;
            }
            return false;
        }
        return true;
    }

	final public function sendForm(Form $form): void
	{
		Linesia::getInstance()->getScheduler()->scheduleDelayedTask(new ClosureTask(function () use ($form) {
			parent::sendForm($form);
		}), 20);
	}

	final public function onUpdate(int $currentTick): bool
	{
		$this->setScoreTag($this->formatNameTag());

		if ($currentTick % (20 * 60) === 0) $this->getStatManager()->recalculateGameTime();

		return parent::onUpdate($currentTick);
	}

	private function formatNameTag(): string
	{
		return str_repeat("§a■", round($this->getHealth())) . str_repeat("§c■", round($this->getMaxHealth() - $this->getHealth()));
	}

	final public function onPostDisconnect(Translatable|string $reason, Translatable|string|null $quitMessage): void
	{
		foreach ($this->cooldowns as $cooldown)
		{
			$cooldown->save($this);
		}
		$this->properties->setProperties("purchasedTags", $this->purchasedTags);
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
		in_array($tag->getName(), $this->purchasedTags, true) ?: $this->purchasedTags[] = $tag->getName();
		$this->tag = $tag;
	}

	final public function hasTag(string $tag): bool
	{
		return in_array($tag, $this->purchasedTags, true);
	}
}