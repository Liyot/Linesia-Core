<?php

namespace UnknowL\handlers;

use pocketmine\utils\Config;
use pocketmine\world\World;
use UnknowL\Linesia;
use UnknowL\player\LinesiaPlayer;

class WorldHandler extends Handler
{

	/**@var $data int[]*/
	private array $data = [];

	private Config $config;
	public function __construct()
	{
		$config = new Config(Linesia::getInstance()->getDataFolder(). "/data/worlds.json", Config::JSON);
		$this->config = $config;
		parent::__construct();
	}

	protected function loadData(): void
    {
		$this->data = $this->config->getAll();
	}

	public function addSpawner(World $world)
	{
		if ($this->canPlace($world))
		{
			isset($this->data[$world->getFolderName()]) ? $this->data[$world->getFolderName()] = 1 : $this->data[$world->getFolderName()] += 1;
		}
	}

	public function canPlace(World $world): bool
	{
		return isset($this->data[$world->getFolderName()]) && $this->data[$world->getFolderName()] < 2;
	}


    protected function saveData(): void
    {
		$config = $this->config;
		$config->setAll($this->data);
		$config->save();
	}

    public function getName(): string
    {
        return "World";
    }

	public function __destruct()
	{
		$this->saveData();
	}
}