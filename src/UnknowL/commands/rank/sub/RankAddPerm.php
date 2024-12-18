<?php

namespace UnknowL\commands\rank\sub;

use pocketmine\command\CommandSender;
use pocketmine\Server;
use UnknowL\handlers\dataTypes\PlayerCooldown;
use UnknowL\handlers\Handler;
use UnknowL\lib\commando\args\StringArgument;
use UnknowL\lib\commando\constraint\InGameRequiredConstraint;
use UnknowL\lib\forms\CustomForm;
use UnknowL\lib\forms\CustomFormResponse;
use UnknowL\lib\forms\element\Dropdown;
use UnknowL\lib\forms\element\Input;
use UnknowL\lib\forms\element\Slider;
use UnknowL\Linesia;
use UnknowL\player\LinesiaPlayer;
use UnknowL\rank\RankManager;
use UnknowL\utils\PathLoader;

class RankAddPerm extends \UnknowL\lib\commando\BaseSubCommand
{

	public function __construct()
	{
		$settings = Linesia::getInstance()->getCommandManager()->getSettings("rank")->getSubSettings("addperm");
		parent::__construct($settings->getName(), $settings->getDescription(), $settings->getAliases());
	}

	/**
     * @inheritDoc
     */
    protected function prepare(): void
    {
		$this->setPermission("rank.add.perm");
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
		$form = new CustomForm("Ajouter une permission", [new Dropdown("Options:", ["Joueur", "Rank"])], function (LinesiaPlayer $player, CustomFormResponse $response)
		{
			switch ($response->getDropdown()->getSelectedOption())
			{
				case "Joueur":
					$options = array_values(array_map(fn($value) => $value->getName(),Server::getInstance()->getOnlinePlayers()));
					$form = new CustomForm("Ajouter une permission", [new Dropdown("Joueur:", $options), new Input("Entrez votre permission", "")],
						function(LinesiaPlayer $player, CustomFormResponse $response)
						{
							Server::getInstance()->getPlayerExact($response->getDropdown()->getSelectedOption())->addPermission($response->getInput()->getValue());
							$player->sendTip("Commande réussie");
						});
					$player->sendForm($form);
					break;

				case "Rank":
					$options = array_keys(Handler::RANK()->getRanks());

					$form = new CustomForm("Ajouter une permission", [new Dropdown("Choississez le grade", $options), new Input("Entrez votre permission", ""),  new Slider("Nombre de jour", 0, 125)],
						function(LinesiaPlayer $player, CustomFormResponse $response)
						{
							$rank = Handler::RANK()->getRank($response->getDropdown()->getSelectedOption());
							$perm = $response->getInput()->getValue();
                            $value = $response->getSlider()->getValue();
                            $path = PathLoader::PATH_RANK_ADD_PERM;
                            $cooldown = $value === 0 ? null : new PlayerCooldown($value * 86400 , $player, $path);
                            is_null($cooldown) ?: $player->addCooldown($cooldown, $path);
							$rank->addPermission($perm);
							$player->sendPopup("La commande à été effectué avec succés");
						});
					$player->sendForm($form);
					break;
			}
		});
		$sender->sendForm($form);
	}
}