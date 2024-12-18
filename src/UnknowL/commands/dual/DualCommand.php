<?php

namespace UnknowL\commands\dual;

use customiesdevs\customies\item\CustomiesItemFactory;
use pocketmine\block\utils\DyeColor;
use pocketmine\command\CommandSender;
use pocketmine\item\Armor;
use pocketmine\item\Item;
use pocketmine\item\StringToItemParser;
use pocketmine\item\VanillaItems;
use pocketmine\math\AxisAlignedBB;
use pocketmine\Server;
use pocketmine\utils\Config;
use pocketmine\world\format\io\GlobalItemDataHandlers as ItemData;
use UnknowL\handlers\dataTypes\requests\DualRequest;
use UnknowL\handlers\dataTypes\requests\MultiDualRequest;
use UnknowL\handlers\Handler;
use UnknowL\lib\commando\args\IntegerArgument;
use UnknowL\lib\commando\args\TargetArgument;
use UnknowL\lib\commando\BaseCommand;
use UnknowL\lib\commando\constraint\InGameRequiredConstraint;
use UnknowL\lib\forms\CustomForm;
use UnknowL\lib\forms\CustomFormResponse;
use UnknowL\lib\forms\element\Dropdown;
use UnknowL\lib\forms\element\Input;
use UnknowL\lib\inventoryapi\inventories\BaseInventoryCustom;
use UnknowL\lib\inventoryapi\InventoryAPI;
use UnknowL\Linesia;
use UnknowL\player\LinesiaPlayer;
use UnknowL\utils\CommandUtils;
use UnknowL\utils\Team;

final class DualCommand extends BaseCommand
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
		$sender->sendForm($this->getForm());

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

	private function getForm(): CustomForm
	{
		$form = new CustomForm("Duels", [new Dropdown("type de duel:", ["1vs1", "2vs2"])],
			function(LinesiaPlayer $player, CustomFormResponse $response)
			{
				$dropdown = $response->getDropdown();
				$options = match ($dropdown->getSelectedOption())
				{
					"2vs2" => [new Input("Nom de votre coéquipier", ""), new Input("Joueur adverse 1", ""), new Input("Joueur adverse 2", "")],
					default => [new Input("Nom du joueur adverse", "")]
				};

				$form = new CustomForm("Choisissez les joueurs", $options, function (LinesiaPlayer $player, CustomFormResponse $response) use ($dropdown) {
                    $server = Server::getInstance();
                    if ($dropdown->getSelectedOption() === "2vs2")
					{
						$mate = $server->getPlayerExact($response->getInput()->getValue());
						$figher1 = $server->getPlayerExact($response->getInput()->getValue());
						$figher2 = $server->getPlayerExact($response->getInput()->getValue());
                        if (is_null($mate) || is_null($figher1) || is_null($figher2))
                        {
                            $player->sendMessage("[LINESIA] Vérifiez les informations que vous avez saisis!");
                            return;
                        }

						Handler::REQUEST()->addRequest(new MultiDualRequest
						(
							$player,
							(new Team([$player, $mate], "Rouge"))->setColor(DyeColor::RED()),
							(new Team([$figher1, $figher2], "Bleu"))->setColor(DyeColor::BLUE())
                        ));
						return;
					}
                    $to = $server->getPlayerExact($response->getInput()->getValue());
                    if (!($to instanceof LinesiaPlayer))
                    {
                        $player->sendMessage("[LINESIA] Vérifiez les informations que vous avez saisis!");
                        return;
                    }
                    new DualRequest($player, $to);
				});
			});
		return $form;
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
			foreach (explode(':', $data["content"]) as $format)
			{
				$item = StringToItemParser::getInstance()->parse($format[0])?->setCount($format[1]);
				if (is_null($item))
				{
					try {
						$item = CustomiesItemFactory::getInstance()->get($format[0])->setCount($format[1]);
					} catch (\Error $error) {}
				}
				$options[$data["name"]] = $item;
			}
		}
		return $options;
	}
}