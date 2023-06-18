<?php

namespace UnknowL\handlers;
use pocketmine\utils\EnumTrait;
use UnknowL\casino\CasinoHandler;
use UnknowL\kits\KitManager;
use UnknowL\rank\RankManager;

/**
 * @method static CooldownHandler COOLDOWN()
 * @method static RankManager RANK()
 * @method static KitManager KIT()
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
			new KitManager(),
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
}