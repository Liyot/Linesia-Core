<?php

namespace UnknowL\player\manager;

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
		return  [];
	}

    public function getAll(): mixed
    {
		return $this->statistics;
	}

    public function getName(): string
    {
		return "Stat";
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

	protected function recalculateGameTime(): void
	{

	}

	final protected function recalculateRatio(): void
	{

	}

	private function setLastConnexion(): void
	{
		$date = new \DateTime('now');
		$date->setTimezone(new \DateTimeZone(\DateTimeZone::EUROPE));
		$this->statistics["lastconnexion"] = $date->format("Y-m-d H:i:s");
		//\IntlDateFormatter::create()
	}

}