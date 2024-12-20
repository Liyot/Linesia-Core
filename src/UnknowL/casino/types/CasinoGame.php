<?php

namespace UnknowL\casino\types;

use UnknowL\lib\forms\BaseForm;
use UnknowL\lib\forms\CustomForm;
use UnknowL\lib\forms\CustomFormResponse;
use UnknowL\lib\forms\element\Input;
use UnknowL\lib\forms\element\Label;
use UnknowL\lib\forms\menu\Button;
use UnknowL\lib\forms\MenuForm;
use UnknowL\player\LinesiaPlayer;

abstract class CasinoGame implements IGame
{
	protected int $mise;

	public function getForm(): BaseForm
	{
		return MenuForm::withOptions
		(
			ucfirst(strtolower($this->getName())),
			"",
			["Description", "Lancer le jeu"],
			function(LinesiaPlayer $player, Button $selected)
			{
				switch ($selected->text)
				{
					case "Description":
						$form = MenuForm::withOptions("Descriptif", $this->getDescription());
						$player->sendForm($form);
						break;

					case "Lancer le jeu":
						if ($this instanceof Roulette)
						{
							$this->start($player);
							return;
						}
						$form = new CustomForm("Choississez votre mise", [new Input("Mise:", "")],
							function (LinesiaPlayer $player, CustomFormResponse $response)
							{
								(int)$mise = $response->getInput()->getValue();
								if($mise > 0)
								{
									if($player->getEconomyManager()->reduce($mise))
									{
										$this->start($player, $mise);
										return;
									}
								}
								$player->sendMessage("Vérifiez les informations");
							});
						$player->sendForm($form);
				}
			});
	}
}