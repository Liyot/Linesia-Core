<?php

namespace UnknowL\commands\tag;

use pocketmine\command\CommandSender;
use UnknowL\handlers\Handler;
use UnknowL\lib\commando\BaseCommand;
use UnknowL\lib\commando\constraint\InGameRequiredConstraint;
use UnknowL\lib\forms\menu\Button;
use UnknowL\lib\forms\MenuForm;
use UnknowL\player\LinesiaPlayer;

final class TagCommand extends BaseCommand
{

    /**
     * @inheritDoc
     */
    protected function prepare(): void
    {
		$this->addConstraint(new InGameRequiredConstraint($this));
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
    }
}