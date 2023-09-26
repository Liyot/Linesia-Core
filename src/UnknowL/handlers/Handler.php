<?php

namespace UnknowL\handlers;
use pocketmine\utils\EnumTrait;
use UnknowL\casino\CasinoHandler;
use UnknowL\games\GameHandler;
use UnknowL\handlers\specific\RepairHandler;
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
 * @method static ChunkHandler CHUNK()
 * @method static TagHandler TAG()
 * @method static OfflineDataHandler OFFLINEDATA()
 * @method static WorldHandler WORLD()
 * @method static ArmorEffectHandler ARMOREFFECTS()
 * @method static GameHandler GAME()
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
			new OfflineDataHandler(),
			new CooldownHandler(),
			new MarketHandler(),
			new ShopHandler(),
			new FactionHandler(),
			new KitHandler(),
			new RankManager(),
			new RequestHandler(),
			new BoxHandler(),
			new ChunkHandler(),
			new TagHandler(),
			new WorldHandler(),
			new ArmorEffectHandler(),
			new GameHandler()
		];
	}

	public static function init(): void
	{
		self::$members = [];
		self::setup();
		RepairHandler::setup();
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
		/**@var Handler $handler*/
		foreach (self::getHandlers() as $handler) {
			self::register($handler);
			$handler->loadData();
		}
		self::_registryRegister("Casino", new CasinoHandler());
	}
}