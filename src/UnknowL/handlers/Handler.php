<?php

namespace UnknowL\handlers;
use pocketmine\utils\EnumTrait;
use UnknowL\casino\CasinoHandler;
use UnknowL\Linesia;
use UnknowL\rank\RankManager;

/**
 * @method static CooldownHandler COOLDOWN()
 * @method static RankManager RANK()
 * @method static KitHandler KIT()
 * @method static MarketHandler MARKET()
 * @method static ShopHandler SHOP()
 * @method static CasinoHandler CASINO()
 * @method static FactionHandler FACTION()
 * @method static RequestHandler REQUEST()
 * @method static BoxHandler BOX()
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
			new FactionHandler(),
			new KitHandler(),
			new RankManager(),
			new RequestHandler(),
			new BoxHandler()
		];
	}

	public static function init(): void
	{
		self::setup();
		self::_registryRegister("Casino", new CasinoHandler());
	}

	public static function saveHandler(): void
	{
		foreach (self::getHandlers() as $handler) {
			Linesia::getInstance()->getLogger()->notice(sprintf("Saving [%s]...", $handler->getName()));
            $handler->saveData();
        }
	}

	protected static function setup(): void
	{
		foreach (self::getHandlers() as $handler) {
			self::register($handler);
		}
	}
}