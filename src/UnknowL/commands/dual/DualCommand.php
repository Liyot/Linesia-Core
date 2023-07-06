<?php

namespace UnknowL\commands\dual;

use pocketmine\command\CommandSender;
use pocketmine\item\Armor;
use pocketmine\item\Item;
use pocketmine\item\VanillaItems;
use pocketmine\utils\Config;
use pocketmine\world\format\io\GlobalItemDataHandlers as ItemData;
use UnknowL\handlers\dataTypes\requests\DualRequest;
use UnknowL\handlers\Handler;
use UnknowL\lib\commando\args\IntegerArgument;
use UnknowL\lib\commando\args\TargetArgument;
use UnknowL\lib\commando\BaseCommand;
use UnknowL\lib\commando\constraint\InGameRequiredConstraint;
use UnknowL\lib\inventoryapi\inventories\BaseInventoryCustom;
use UnknowL\lib\inventoryapi\InventoryAPI;
use UnknowL\Linesia;
use UnknowL\player\LinesiaPlayer;
use UnknowL\utils\CommandUtils;

class DualCommand extends BaseCommand
{

	public function __construct()
	{
		$settings = Linesia::getInstance()->getCommandManager()->getSettings('dual');
		parent::__construct(Linesia::getInstance(), $settings->getName(), $settings->getDescription(), $settings->getAliases());
	}


	/**
     * @inheritDoc
     */
    protected function prepare(): void
    {
		$this->addConstraint(new InGameRequiredConstraint($this));
		$this->registerArgument(0, new IntegerArgument("mise"));
		$this->registerArgument(1, new TargetArgument("joueur"));
		$this->setPermission("pocketmine.group.user");
	}

    /**
     * @inheritDoc
	 * @param LinesiaPlayer $sender
     */
    public function onRun(CommandSender $sender, string $aliasUsed, array $args): void
    {
		if (isset($args["mise"]) && is_int((int)$args["mise"]))
		{
			if((($target = CommandUtils::checkTarget($args['joueur'])) !== null) && $target->getDisplayName() !== $sender->getDisplayName())
			{
				$form = InventoryAPI::createSimpleChest(true)->setName("Choissisez votre kit");
				$form->setContents(array_map(fn(string $data) => VanillaItems::DIAMOND_CHESTPLATE()->setCustomName($data), array_keys($this->getDualKits())));
				$form->addItem(VanillaItems::DIAMOND_CHESTPLATE()->setCustomName("Votre propre équippement"));
				$form->setClickListener(function (LinesiaPlayer $player, BaseInventoryCustom $inventory, Item $sourceItem, Item $targetItem, int $slot) use ($args, $sender, $target)
				{
					$kit = [];
					array_map(function (string $name) use ($sender, $target, $player, $sourceItem, &$kit)
					{
						if ($name === $sourceItem->getCustomName())
						{
							$target->sendMessage(sprintf("[Duel] Le joueur %s vous invite en duel pour accepter taper 'accept' sinon taper 'decline'", $sender->getDisplayName()));
							$kit = $this->sortInventory($this->getDualKits()[$name]);
							return;
						}
					}, array_values($this->getDualKits()));
					Handler::REQUEST()->addRequest(new DualRequest($player, $target,$kit, (int)$args['mise']));
				});
				$form->send($sender);
			}
			return;
		}
		$sender->sendMessage("Vérifiez les informations que vous avez saisi");
	}

	/**
	 * @param Item[] $inventory
	 * @return array
	 */
	private function sortInventory(array $inventory): array
	{
		$kit = [];
		foreach ($inventory as $item)
		{
			if ($item instanceof Armor)
			{
				$kit["armor"][] = $item;
				continue;
			}
			$kit['inventory'][] = $item;
		}
		return $kit;
	}

	/**
	 * @return array
	 */
	private function getDualKits(): array
	{
		$config = new Config(sprintf("%s/dualkit.json",Linesia::getInstance()->getDataFolder()), Config::JSON);
		$options = [];
		foreach ($config->getAll(true) as $data)
		{
			$format =
			$options[$data["name"]] = array_map
			(
				fn(array $format) => ItemData::getDeserializer()->deserializeStack(ItemData::getUpgrader()->upgradeItemTypeDataInt($format[0], $format[1], $format[2], null))
				, explode(":", $data["content"])
			);
		}
		return $options;
	}
}