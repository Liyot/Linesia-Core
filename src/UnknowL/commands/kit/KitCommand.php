<?php

namespace UnknowL\commands\kit;

use pocketmine\command\CommandSender;
use UnknowL\handlers\Handler;
use UnknowL\handlers\dataTypes\Kit;
use UnknowL\lib\commando\BaseCommand;
use UnknowL\lib\commando\constraint\InGameRequiredConstraint;
use UnknowL\lib\forms\menu\Button;
use UnknowL\lib\forms\MenuForm;
use UnknowL\Linesia;
use UnknowL\player\LinesiaPlayer;

final class KitCommand extends BaseCommand
{

	public function __construct()
	{
		$setting = Linesia::getInstance()->getCommandManager()->getSettings("kit");
		parent::__construct(Linesia::getInstance(), $setting->getName(), $setting->getDescription(), $setting->getAliases());
	}

	protected function prepare(): void
	{
		$this->setPermission("pocketmine.group.user");
		$this->addConstraint(new InGameRequiredConstraint($this));
	}

	/**
	 * @param LinesiaPlayer $sender
	 * @param string $aliasUsed
	 * @param array $args
	 * @return void
	 */
	public function onRun(CommandSender $sender, string $aliasUsed, array $args): void
	{
		$options = array_map(fn(Kit $value) => $value->getName(), Handler::KIT()->getKits());
		$sender->sendForm(MenuForm::withOptions("Kit", "", $options, function (LinesiaPlayer $player, Button $button){
			$form = MenuForm::withOptions($button->text, "", ["Obtenir", "Prévisualiser"], function (LinesiaPlayer $player, Button $selected) use ($button)
			{
				$kit = Handler::KIT()->getKit($button->text);
				match ($selected->text) {
					"Obtenir" => $kit->send($player),
					"Prévisualiser" => $kit->previsualize()->send($player)
				};
			});
			$player->sendForm($form);
		}));
	}
}