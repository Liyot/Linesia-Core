<?php

namespace UnknowL\handlers\dataTypes;

use Cassandra\Date;
use pocketmine\Server;
use UnknowL\player\LinesiaPlayer;

class PlayerCooldown extends Cooldown
{

	public function __construct(private \DateTime $cooldownTime, private LinesiaPlayer $player, private string $path, private bool $days = false, private ?\DateTime $initialTime = null)
	{
		$player->addCooldown($this, $path);
		isset($this->initialTime) ?: $this->initialTime = \DateTime::createFromFormat("d:H:i:s", \date("d:H:i:s"));
		parent::__construct($this->cooldownTime);
	}

	final public function format(): string
	{
		$date1 = clone $this->initialTime;
		$date2 = \DateTime::createFromFormat("d:H:i:s", date("d:H:i:s"));
		$date1->add(new \DateInterval(sprintf("P%02dDT%02dH%02dM%02dS", $this->cooldownTime->format("d"), $this->cooldownTime->format("H"),
			$this->cooldownTime->format("i"), $this->cooldownTime->format("s"))));
		$diff = $date1->sub(new \DateInterval(sprintf("P%02dDT%02dH%02dM%02dS", $date2->format("d"), $date2->format("H"), $date2->format("i"), $date2->format("s"))));
		$ex = explode(":", $diff->format("d:H:i:s"));
		$days = $ex[0];
		if($this->days) $days = 0;
		return sprintf("Il reste %s jours %s heures %s minutes et %s secondes", $days, $ex[1], $ex[2], $ex[3]);
	}

	final public function end(): bool
	{
		if(($this->initialTime->getTimestamp() + $this->cooldownTime->getTimestamp()) < time())
		{
			var_dump(time(), $this->cooldownTime->getTimestamp() );
			$this->save($this->player);
			return true;
		}
		return false;
	}


	final public function save(LinesiaPlayer $player): void
	{
		$player->getPlayerProperties()->setNestedProperties($this->path, $this->internalFormat());
	}

	final public function internalFormat(): string
	{
		return (sprintf("%s;%s;%s", $this->cooldownTime->format("d:H:i:s"), $this->path, $this->initialTime->format("d:H:i:s")));
	}
}