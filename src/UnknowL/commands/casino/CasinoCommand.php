<?php

namespace UnknowL\commands\casino;

use pocketmine\command\CommandSender;
use UnknowL\handlers\Handler;
use UnknowL\lib\commando\BaseCommand;
use UnknowL\lib\commando\constraint\InGameRequiredConstraint;
use UnknowL\Linesia;
use UnknowL\player\LinesiaPlayer;

class CasinoCommand extends BaseCommand
{

	public function __construct()
	{
		$setting = Linesia::getInstance()->getCommandManager()->getSettings("casino");
		parent::__construct(Linesia::getInstance(),$setting->getName(), $setting->getDescription(), $setting->getAliases());
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
		$sender->sendForm(Handler::CASINO()->getForm());
    }
}