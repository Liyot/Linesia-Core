<?php

namespace UnknowL\commands\shop\sub;

use pocketmine\command\CommandSender;
use pocketmine\item\VanillaItems;
use UnknowL\commands\CommandManager;
use UnknowL\handlers\dataTypes\ShopData;
use UnknowL\handlers\Handler;
use UnknowL\lib\commando\args\IntegerArgument;
use UnknowL\lib\commando\BaseSubCommand;
use UnknowL\Linesia;
use UnknowL\player\LinesiaPlayer;

class ShopSellCommand extends BaseSubCommand
{

	public function __construct()
	{
		$settings = Linesia::getInstance()->getCommandManager()->getSettings("shop")->getSubSettings("sell");
		parent::__construct($settings->getName(), $settings->getDescription(), $settings->getAliases());
	}

	/**
     * @inheritDoc
     */
    protected function prepare(): void
    {
		$this->registerArgument(0, new IntegerArgument("price"));
		$this->setPermission("pocketmine.group.user");
	}

	/**@var LinesiaPlayer $sender*/
    public function onRun(CommandSender $sender, string $aliasUsed, array $args): void
    {
		$item = $sender->getInventory()->getItemInHand();
		if ($item->getTypeId() !== VanillaItems::AIR()->getTypeId())
		{
			Handler::SHOP()->addSellable(new ShopData($sender->getName(),$args["price"], Handler::SHOP(), $item, time() + 3600 * 24 * 2));
			$sender->getInventory()->removeItem($item);
			$sender->sendMessage("[Linesia] §aVotre item a bien été ajouté à la vente");
			return;
		}
		$sender->sendMessage("[Linesia] §cVous n'avez pas d'item en main");
	}
}