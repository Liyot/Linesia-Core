<?php

namespace UnknowL\commands\stat;

use JetBrains\PhpStorm\ArrayShape;
use pocketmine\command\CommandSender;
use pocketmine\Server;
use UnknowL\commands\CommandManager;
use UnknowL\lib\commando\args\TargetArgument;
use UnknowL\lib\commando\constraint\InGameRequiredConstraint;
use UnknowL\lib\forms\MenuForm;
use UnknowL\Linesia;
use UnknowL\player\LinesiaPlayer;

final class StatCommand extends \UnknowL\lib\commando\BaseCommand
{

	public function __construct()
	{
		$settings = Linesia::getInstance()->getCommandManager()->getSettings('stat');
		parent::__construct(Linesia::getInstance(), $settings->getName(), $settings->getDescription(), $settings->getAliases());
	}

    /**
     * @inheritDoc
     */
    protected function prepare(): void
    {
		$this->addConstraint(new InGameRequiredConstraint($this));
		$this->registerArgument(0, new TargetArgument('joueur', true));
		$this->setPermission("pocketmine.group.user");
	}

    /**
     * @param LinesiaPlayer $sender
     */
    public function onRun(CommandSender $sender, string $aliasUsed, array $args): void
    {
		if (empty($args))
		{
			$form = MenuForm::withOptions("Vos statistiques", (string)$sender->getStatManager());
			$sender->sendForm($form);
			return;
		}
		/**@var LinesiaPlayer $target*/
		if (($target = Server::getInstance()->getPlayerExact($args["joueur"])) !== null)
		{
			$sender->sendForm(MenuForm::withOptions("Vos statistiques", (string)$target->getStatManager()));
			return;
		}

		if (Server::getInstance()->getOfflinePlayerData($args["joueur"]) !== null)
		{
			$sender->sendForm(MenuForm::withOptions("Vos statistiques", (string)$sender->getStatManager()->getOfflinePlayerStatistics($args["joueur"])));
			return;
		}
		$sender->sendMessage(sprintf("Le joueur %s n'as pas été trouvé", $args["joueur"]));
	}
}