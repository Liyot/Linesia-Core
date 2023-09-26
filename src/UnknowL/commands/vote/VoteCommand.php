<?php

namespace UnknowL\commands\vote;

use pocketmine\command\CommandSender;
use pocketmine\item\VanillaItems;
use pocketmine\lang\Translatable;
use pocketmine\plugin\Plugin;
use pocketmine\scheduler\AsyncTask;
use pocketmine\Server;
use pocketmine\utils\Internet;
use pocketmine\utils\InternetRequestResult;
use pocketmine\utils\SingletonTrait;
use UnknowL\api\ScoreBoardAPI;
use UnknowL\commands\CommandSettings;
use UnknowL\lib\commando\BaseCommand;
use UnknowL\lib\commando\constraint\InGameRequiredConstraint;
use UnknowL\Linesia;
use UnknowL\player\LinesiaPlayer;

class VoteCommand extends BaseCommand
{
	use SingletonTrait;

	private int $voteParty = 0;

	public function __construct()
	{
		self::setInstance($this);
		$this->voteParty = Linesia::getInstance()->getConfig()->get('voteParty', 0);
		$settings = Linesia::getInstance()->getCommandManager()->getSettings("vote");
		parent::__construct(Linesia::getInstance(), $settings->getName(), $settings->getDescription(), $settings->getAliases());
	}

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
				$this->result = Internet::simpleCurl(sprintf("https://minecraftpocket-servers.com/api/?object=votes&element=claim&key=%s&username=%s", VoteCommand::VOTEAPI_KEY, $this->player))->getBody();
			}

			public function onCompletion(): void
			{
				parent::onCompletion();
				/**@var LinesiaPlayer $player*/
				$player = Server::getInstance()->getPlayerExact($this->player);
				switch ($this->result)
				{
					case 1:
						$player->sendMessage("§aVous avez reçu la récompense de votre vote !");
                        $player->getInventory()->addItem(VanillaItems::BOOK()->setCount(2));
						VoteCommand::getInstance()->updateVoteParty($player);
						if (!isset($this->player)) return ;
						Internet::simpleCurl(sprintf('https://minecraftpocket-servers.com/api/?action=post&object=votes&element=claim&key=%s&username=%s', VoteCommand::VOTEAPI_KEY, $player->getName()));
						break;

					case 2:
						$player->sendMessage("§cVous avez déjà reçu la récompense de votre vote!");
						break;

					default:
						$player->sendMessage("§cVous n'avez pas encore voté aujourd'hui!");
						break;
				}
			}
		});
	}

	public function updateVoteParty(LinesiaPlayer $player): void
	{
		$this->voteParty++;
		if ($this->voteParty >= 100)
		{
			/**@var LinesiaPlayer $player*/
			foreach (Server::getInstance()->getOnlinePlayers() as $player)
			{
				$player->getInventory()->addItem(VanillaItems::BOOK()->setCount(2));
				$player->getEconomyManager()->add(2500);
			}
			ScoreBoardAPI::updateVoteParty($player, $this->getVoteParty());
			Server::getInstance()->broadcastMessage("§aLa vote party est terminée, vous avez reçu les récompenses!");
			$this->voteParty = 0;
		}
	}

	/**
	 * @return int
	 */
	public function getVoteParty(): int
	{
		return $this->voteParty;
	}
}