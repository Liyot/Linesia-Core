<?php

namespace UnknowL\player\manager;

use DateTimeInterface;
use JetBrains\PhpStorm\ArrayShape;

final class StatManager extends PlayerManager
{

	public const TYPE_KILL = 0;

	public const TYPE_DEATH = 1;

	public const TYPE_BLOCK_PLACED = 2;

	public const TYPE_BLOCK_MINED = 3;

	public const TYPE_CONNEXION = 4;

	public const TYPE_DUAL = 5;

	#[ArrayShape([
		'kill' => 'int',
		'death' => 'int',
		'killstreak' => 'int',
		'kd' => 'float',
		'gametime' => 'string',
		'blockmined' => 'int',
		'blockposed' => 'int',
		'firstconnexion' => 'string',
		'lastconnexion' => 'string',
		'dualwon' => 'int'
	])]
	private array $statistics = [];

    protected function load(): void
    {
		$this->statistics = $this->deserialize();
	}

	private function deserialize(): array
	{
		return $this->getPlayer()->getPlayerProperties()->getProperties("statistics");
	}

    public function getAll(): array
    {
		return $this->statistics;
	}

    public function getName(): string
    {
		return "statistics";
	}

	public function handleEvents(int $types): void
	{
		match ($types)
		{
			self::TYPE_KILL => $this->addKill(),
			self::TYPE_DEATH => $this->addDeath(),
			self::TYPE_BLOCK_PLACED => $this->addPosedBlock(),
			self::TYPE_BLOCK_MINED => $this->addMinedBlock(),
			self::TYPE_CONNEXION => $this->setLastConnexion(),
			self::TYPE_DUAL => $this->updateDual()
		};
	}

	private function addKill(): void
	{
		$this->statistics["kill"] += 1;
		$this->recalculateRatio();
	}

	private function addDeath(): void
	{
		$this->statistics["death"] += 1;
		$this->recalculateRatio();
	}

	private function updateDual(): void
	{
		//TODO: when dual are done
	}

	private function addMinedBlock(): void
	{
		$this->statistics["blockmined"] += 1;
	}

	private function addPosedBlock(): void
	{
		$this->statistics["blockposed"] += 1;
	}

	final public function recalculateGameTime(): void
	{
        $gametime = \DateTime::createFromFormat(\DateTimeInterface::ATOM,$this->statistics["gametime"]);
        $lastConnexion = \DateTime::createFromFormat(DateTimeInterface::ATOM, $this->statistics["lastconnexion"]);
        $interval = $lastConnexion->diff($gametime);
        var_dump($interval);
	}

    final public function formatGameTime(): string
    {
        $data = explode(':', $this->statistics["gametime"]);
        return sprintf("%d mois %d jours %d et %d heures ", $data[0], $data[1], $data[2], $data[3]);
    }

	final protected function recalculateRatio(): void
	{
        $kd = round($this->statistics['kill'] / $this->statistics['death'], 2);
        $this->statistics["kd"] = $kd;
	}

	private function setLastConnexion(): void
	{
		$date = new \DateTime('now');
		$date->setTimezone(new \DateTimeZone(\DateTimeZone::EUROPE));
		$this->statistics["lastconnexion"] = $date->format(DateTimeInterface::ATOM);
	}
}