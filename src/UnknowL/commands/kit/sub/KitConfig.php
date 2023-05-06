<?php

namespace UnknowL\commands\kit\sub;

use pocketmine\command\CommandSender;
use UnknowL\lib\commando\BaseSubCommand;
use UnknowL\lib\commando\constraint\InGameRequiredConstraint;
use UnknowL\lib\forms\CustomForm;
use UnknowL\lib\forms\CustomFormResponse;
use UnknowL\lib\forms\element\Input;
use UnknowL\player\LinesiaPlayer;

final class KitConfig extends BaseSubCommand
{

	protected function prepare(): void
	{
		$this->addConstraint(new InGameRequiredConstraint($this));
		$this->setPermission("kit.config");
	}

	/**
	 * @param LinesiaPlayer $sender
	 * @param string $aliasUsed
	 * @param array $args
	 * @return void
	 */
	public function onRun(CommandSender $sender, string $aliasUsed, array $args): void
	{
		global $name;
		$form = new CustomForm("Nom du kit", [new Input("Nom de votre nouveau kit", "Tapez un nom")],
			function (LinesiaPlayer $player, CustomFormResponse $response)
			{
			});
	}
}