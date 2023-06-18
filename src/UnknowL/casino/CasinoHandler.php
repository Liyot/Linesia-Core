<?php

namespace UnknowL\casino;

use pocketmine\utils\RegistryTrait;
use pocketmine\world\Position;
use UnknowL\casino\types\CasinoGame;
use UnknowL\casino\types\Escalier;
use UnknowL\casino\types\Roulette;
use UnknowL\handlers\Handler;
use UnknowL\lib\forms\menu\Button;
use UnknowL\lib\forms\MenuForm;
use UnknowL\player\LinesiaPlayer;

/**
 * @method static Roulette ROULETTE()
 * @method static Escalier ESCALIER()
 */
final class CasinoHandler extends Handler
{
	use RegistryTrait;

	public function __construct()
	{
		self::setup();
		parent::__construct();
	}

	protected static function setup(): void
	{
		self::_registryRegister("roulette", new Roulette());
		self::_registryRegister("escalier", new Escalier());
	}

	final public function getForm(): MenuForm
	{
		return MenuForm::withOptions("Choissis ton jeu", "", array_map(fn($value) => ucfirst(strtolower($value)), array_keys(self::$members)),
			function (LinesiaPlayer $player, Button $selected)
			{
				$function = strtoupper($selected->text);
				/**
				 * @var CasinoGame $game
				 */
				$game = self::$function();
				$player->sendForm($game->getForm());
			});
	}

	protected function loadData(): void
	{}

	protected function saveData(): void
	{}

	public function getName(): string
	{
		return "Casino";
	}
}