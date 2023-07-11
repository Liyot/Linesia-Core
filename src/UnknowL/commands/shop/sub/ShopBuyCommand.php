<?php

namespace UnknowL\commands\shop\sub;

use pocketmine\command\CommandSender;
use UnknowL\handlers\Handler;
use UnknowL\lib\commando\BaseSubCommand;
use UnknowL\lib\commando\constraint\InGameRequiredConstraint;
use UnknowL\Linesia;
use UnknowL\player\LinesiaPlayer;

class ShopBuyCommand extends BaseSubCommand
{

	public function __construct()
	{
		$settings = Linesia::getInstance()->getCommandManager()->getSettings("shop")->getSubSettings("buy");
		parent::__construct($settings->getName(), $settings->getDescription(), $settings->getAliases());
	}

    /**
     * @inheritDoc
     */
    protected function prepare(): void
    {
		$this->addConstraint(new InGameRequiredConstraint($this));
		$this->setPermission("pocketmine.group.user");
	}

	/**@var LinesiaPlayer $sender*/
    public function onRun(CommandSender $sender, string $aliasUsed, array $args): void
    {
		Handler::SHOP()->getForm()->send($sender);
	}
}