<?php

namespace UnknowL\commands\vote;

use pocketmine\command\CommandSender;
use pocketmine\scheduler\AsyncTask;
use pocketmine\Server;
use pocketmine\utils\Internet;
use pocketmine\utils\InternetRequestResult;
use UnknowL\lib\commando\BaseCommand;
use UnknowL\lib\commando\constraint\InGameRequiredConstraint;
use UnknowL\Linesia;

class VoteCommand extends BaseCommand
{

	const VOTEAPI_KEY = 'hPAhupWrjd6WGFRIOHE6nKGkPyfVVmKVlt';

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
     */
    public function onRun(CommandSender $sender, string $aliasUsed, array $args): void
    {
		Server::getInstance()->getAsyncPool()->submitTask(new class($sender->getName()) extends AsyncTask
		{
			private int $result;

			public function __construct
			(
				private string $player
			){}
			public function onRun(): void
			{
				$this->result = Internet::simpleCurl(sprintf("https://minecraftpocket-servers.com/api/?object=votes&element=claim&key=%s&username=%s", VoteCommand::VOTEAPI_KEY, 'xLitozz'))->getBody();
			}

			public function onCompletion(): void
			{
				parent::onCompletion();
				$player = Server::getInstance()->getPlayerExact($this->player);
				switch ($this->result)
				{
					case 1:
						$player->sendMessage("Vous avez reçu la récompense de votre vote!");
						Internet::simpleCurl(sprintf('https://minecraftpocket-servers.com/api/?action=post&object=votes&element=claim&key=%s&username=%s', VoteCommand::VOTEAPI_KEY, $this->player));
						break;

					case 2:
						$player->sendMessage("Vous avez déjà reçu la récompense de votre vote!");
						break;

					default:
						$player->sendMessage("Vous n'avez pas encore voté aujourd'hui!");
						break;
				}
			}
		});
	}
}