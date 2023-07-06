<?php

namespace UnknowL\handlers;
use pocketmine\command\CommandSender;
use pocketmine\player\Player;
use pocketmine\utils\EnumTrait;
use UnknowL\casino\CasinoHandler;
use UnknowL\rank\RankManager;

/**
 * @method static CooldownHandler COOLDOWN()
 * @method static RankManager RANK()
 * @method static KitHandler KIT()
 * @method static MarketHandler MARKET()
 * @method static ShopHandler SHOP()
 * @method static CasinoHandler CASINO()
 * @method static FactionHandler FACTION()
 */
abstract class Handler
{
	protected abstract function loadData(): void;

	protected abstract function saveData(): void;

	public abstract function getName(): string;

	use EnumTrait {
		__construct as Enum__construct;
	}

	public function __construct()
	{
		$this->Enum__construct(strtolower($this->getName()));
	}

	private static function getHandlers(): array
	{
		return [
			new CooldownHandler(),
			new MarketHandler(),
			new ShopHandler(),
			new CasinoHandler(),
			new FactionHandler(),
			new KitHandler(),
			new RankManager(),
		];
	}

	public static function init(): void
	{
		self::setup();
	}

	protected static function setup(): void
	{
		foreach (self::getHandlers() as $handler) {
			self::register($handler);
		}
	}

    public static function getPlayerName($player): string
    {
        if ($player instanceof Player) return $player->getDisplayName(); elseif ($player instanceof CommandSender) return "Serveur";
        else return $player;
    }
}