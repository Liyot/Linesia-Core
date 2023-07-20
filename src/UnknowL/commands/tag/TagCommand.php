<?php

namespace UnknowL\commands\tag;

use pocketmine\command\CommandSender;
use pocketmine\lang\Translatable;
use pocketmine\plugin\Plugin;
use UnknowL\commands\CommandManager;
use UnknowL\handlers\Handler;
use UnknowL\lib\commando\BaseCommand;
use UnknowL\lib\commando\constraint\InGameRequiredConstraint;
use UnknowL\lib\forms\menu\Button;
use UnknowL\lib\forms\MenuForm;
use UnknowL\Linesia;
use UnknowL\player\LinesiaPlayer;

final class TagCommand extends BaseCommand
{

	public function __construct()
	{
		$settings = Linesia::getInstance()->getCommandManager()->getSettings("tag");
		parent::__construct(Linesia::getInstance(), $settings->getName(), $settings->getDescription(), $settings->getAliases());
	}

	/**
     * @inheritDoc
     */
    protected function prepare(): void
    {
		$this->addConstraint(new InGameRequiredConstraint($this));
		$this->setPermission("pocketmine.group.user");
	}

    /**
     * @inheritDoc
	 * @param LinesiaPlayer $sender
     */
    public function onRun(CommandSender $sender, string $aliasUsed, array $args): void
    {
        $form = MenuForm::withOptions("Choississez un tag", "", array_keys(Handler::TAG()->getTags()),
			function (LinesiaPlayer $player, Button $selected)
			{
				$options = $player->hasTag($selected->text) ? ["Activé", "Désactivé"] : ["Acheter ce tag"];
				$form = MenuForm::withOptions("Choississez une option", "", $options, function (LinesiaPlayer $player, Button $selected)
				{
					match ($selected->text)
					{
						"Activé" => $player->setTag(Handler::TAG()->getTag($selected->text)),
						"Désactivé" => $player->getTag(),
						default => Handler::TAG()->buyTag($player, Handler::TAG()->getTag($selected->text))
					};
				});
			});
		$sender->sendForm($form);
    }
}